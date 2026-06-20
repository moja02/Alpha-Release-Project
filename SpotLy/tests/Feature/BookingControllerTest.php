<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Account;
use App\Models\Booking;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Event;
use Carbon\Carbon;
use App\Events\BookingExpiredEvent;
use App\Mail\LateExitNotification;

class BookingControllerTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Event::fake();
        $this->withSession([]);
    }

    /*
    |--------------------------------------------------------------------------
    | الدوال المساعدة للتهيئة (Helper Functions)
    |--------------------------------------------------------------------------
    */

    protected function createDriverAccount($email = 'driver@test.com', $plateNumber = 'PLATE-111', $walletBalance = 100.00)
    {
        $account = Account::create([
            'name' => 'Driver User',
            'email' => $email,
            'phone' => '0912345678',
            'password' => bcrypt('secret123'),
            'role' => 'user',
        ]);

        DB::table('users')->insert([
            'id' => $account->id,
            'account_id' => $account->id,
            'plate_number' => $plateNumber,
            'status' => 'active',
            'fake_booking_count' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('wallets')->insert([
            'user_id' => $account->id,
            'balance' => $walletBalance,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $account;
    }

    protected function createEmployeeAccount($email = 'employee@test.com')
    {
        $account = Account::create([
            'name' => 'Employee User',
            'email' => $email,
            'phone' => '0912345679',
            'password' => bcrypt('secret123'),
            'role' => 'employee',
        ]);

        $employeeId = DB::table('employees')->insertGetId([
            'account_id' => $account->id,
            'bank_account_number' => 'LY123456789012345678',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'account' => $account,
            'employee_id' => $employeeId,
        ];
    }

    protected function createParking($name = 'Test Parking', $capacity = 10, $employeeId = null)
    {
        return DB::table('parkings')->insertGetId([
            'name' => $name,
            'location_park' => 'Test Location',
            'total_capacity' => $capacity,
            'available_capacity' => $capacity,
            'latitude' => 32.895456,
            'longitude' => 13.180324,
            'employee_id' => $employeeId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | اختبارات جلب وتوصية المواقف (Spots & Recommendations)
    |--------------------------------------------------------------------------
    */

    public function test_get_spots_success()
    {
        $empData = $this->createEmployeeAccount();
        $parkingId = $this->createParking('Central Spot', 15, $empData['employee_id']);

        $response = $this->getJson('/api/parkings/spots');

        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success')
                 ->assertJsonFragment(['name' => 'Central Spot', 'employee_bank_account' => 'LY123456789012345678']);
    }

    public function test_get_recommended_spots_wsm()
    {
        // تهيئة 3 مواقف بأبعاد وسعات مختلفة
        // الموقف الأول: قريب وذو سعة جيدة
        DB::table('parkings')->insert([
            'name' => 'Spot Near',
            'location_park' => 'Loc 1',
            'total_capacity' => 10,
            'available_capacity' => 8,
            'latitude' => 32.895000,
            'longitude' => 13.180000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // الموقف الثاني: بعيد
        DB::table('parkings')->insert([
            'name' => 'Spot Far',
            'location_park' => 'Loc 2',
            'total_capacity' => 10,
            'available_capacity' => 9,
            'latitude' => 32.950000,
            'longitude' => 13.250000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->getJson('/api/parkings/recommend?latitude=32.895456&longitude=13.180324&sort_by=wsm');

        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success');

        $data = $response->json('data');
        $this->assertCount(2, $data);
        // الترتيب حسب خوارزمية WSM: القريب يجب أن يكون الأول
        $this->assertEquals('Spot Near', $data[0]['name']);
        $this->assertEquals('Spot Far', $data[1]['name']);
    }

    public function test_get_recommended_spots_sorted_by_available_capacity()
    {
        DB::table('parkings')->insert([
            'name' => 'Low Capacity',
            'location_park' => 'Loc 1',
            'total_capacity' => 10,
            'available_capacity' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('parkings')->insert([
            'name' => 'High Capacity',
            'location_park' => 'Loc 2',
            'total_capacity' => 10,
            'available_capacity' => 9,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->getJson('/api/parkings/recommend?sort_by=available_capacity');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals('High Capacity', $data[0]['name']);
    }

    /*
    |--------------------------------------------------------------------------
    | اختبارات الحجز النشط (Active Booking)
    |--------------------------------------------------------------------------
    */

    public function test_get_active_booking_none()
    {
        $driver = $this->createDriverAccount();

        $response = $this->getJson('/api/bookings/active?userId=' . $driver->id);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success',
                     'hasActiveBooking' => false
                 ]);
    }

    public function test_get_active_booking_exists()
    {
        $driver = $this->createDriverAccount();
        $parkingId = $this->createParking();

        DB::table('bookings')->insert([
            'user_id' => $driver->id,
            'parking_id' => $parkingId,
            'plate_number' => 'PLATE-111',
            'start_time' => now(),
            'end_time' => now()->addHour(),
            'type' => 'actual',
            'status' => 'confirmed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->getJson('/api/bookings/active?userId=' . $driver->id);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success',
                     'hasActiveBooking' => true
                 ])
                 ->assertJsonStructure(['bookingData']);
    }

    /*
    |--------------------------------------------------------------------------
    | اختبارات إنشاء الحجز (Create Booking)
    |--------------------------------------------------------------------------
    */

    public function test_create_initial_booking_success()
    {
        $driver = $this->createDriverAccount();
        $parkingId = $this->createParking('My Parking', 5);

        $response = $this->postJson('/api/bookings/create', [
            'userId' => $driver->id,
            'parkingId' => $parkingId,
            'bookingType' => 'initial'
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('bookings', [
            'user_id' => $driver->id,
            'parking_id' => $parkingId,
            'type' => 'initial',
            'status' => 'confirmed',
            'cost' => 0.00
        ]);

        // التحقق من إنقاص السعة المتاحة
        $this->assertDatabaseHas('parkings', [
            'id' => $parkingId,
            'available_capacity' => 4
        ]);

        // التحقق من إدراج إشعار
        $this->assertDatabaseHas('notifications', [
            'user_id' => $driver->id,
            'type' => 'Booking_Confirmed'
        ]);
    }

    public function test_create_actual_booking_success_with_payment()
    {
        $driver = $this->createDriverAccount('driver@test.com', 'PLATE-111', 100.00);
        $parkingId = $this->createParking('My Parking', 5);

        $startTime = now()->addHour();
        $endTime = now()->addHours(3); // مدة ساعتين -> التكلفة = 2 * 2.5 = 5 نقاط

        $response = $this->postJson('/api/bookings/create', [
            'userId' => $driver->id,
            'parkingId' => $parkingId,
            'bookingType' => 'actual',
            'startTime' => $startTime->toDateTimeString(),
            'endTime' => $endTime->toDateTimeString(),
        ]);

        $response->assertStatus(201);

        // التحقق من خصم الرصيد
        $this->assertDatabaseHas('wallets', [
            'user_id' => $driver->id,
            'balance' => 95.00
        ]);

        $this->assertDatabaseHas('bookings', [
            'user_id' => $driver->id,
            'parking_id' => $parkingId,
            'type' => 'actual',
            'cost' => 5.00,
            'status' => 'confirmed'
        ]);
    }

    public function test_create_actual_booking_fails_insufficient_balance()
    {
        $driver = $this->createDriverAccount('driver@test.com', 'PLATE-111', 2.00); // رصيد 2 نقطة فقط
        $parkingId = $this->createParking();

        $startTime = now()->addHour();
        $endTime = now()->addHours(3); // مدة ساعتين -> التكلفة = 5 نقاط

        $response = $this->postJson('/api/bookings/create', [
            'userId' => $driver->id,
            'parkingId' => $parkingId,
            'bookingType' => 'actual',
            'startTime' => $startTime->toDateTimeString(),
            'endTime' => $endTime->toDateTimeString(),
        ]);

        $response->assertStatus(400)
                 ->assertJsonPath('status', 'error')
                 ->assertJsonPath('message', 'رصيدك غير كافٍ. تكلفة الحجز 5 نقطة.');
    }

    public function test_create_booking_fails_double_booking()
    {
        $driver = $this->createDriverAccount();
        $parkingId = $this->createParking();

        // إنشاء حجز نشط مسبقاً
        DB::table('bookings')->insert([
            'user_id' => $driver->id,
            'parking_id' => $parkingId,
            'plate_number' => 'PLATE-111',
            'start_time' => now(),
            'end_time' => now()->addMinutes(30),
            'type' => 'initial',
            'status' => 'confirmed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->postJson('/api/bookings/create', [
            'userId' => $driver->id,
            'parkingId' => $parkingId,
            'bookingType' => 'initial'
        ]);

        $response->assertStatus(400)
                 ->assertJsonPath('message', 'لديك حجز نشط بالفعل.');
    }

    public function test_create_booking_fails_when_parking_is_full()
    {
        $driver = $this->createDriverAccount();
        $parkingId = $this->createParking('Full Parking', 0); // السعة 0

        $response = $this->postJson('/api/bookings/create', [
            'userId' => $driver->id,
            'parkingId' => $parkingId,
            'bookingType' => 'initial'
        ]);

        $response->assertStatus(400)
                 ->assertJsonPath('message', 'عذراً، هذه الساحة ممتلئة بالكامل.');
    }

    /*
    |--------------------------------------------------------------------------
    | اختبارات إلغاء الحجز والتعويض (Cancel Booking)
    |--------------------------------------------------------------------------
    */

    public function test_cancel_initial_booking_success()
    {
        $driver = $this->createDriverAccount();
        $parkingId = $this->createParking('Parking', 5);

        $bookingId = DB::table('bookings')->insertGetId([
            'user_id' => $driver->id,
            'parking_id' => $parkingId,
            'plate_number' => 'PLATE-111',
            'start_time' => now(),
            'end_time' => now()->addMinutes(30),
            'type' => 'initial',
            'status' => 'confirmed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // تخفيض سعة الموقف المتاحة يدوياً لمطابقة عملية الحجز
        DB::table('parkings')->where('id', $parkingId)->decrement('available_capacity', 1);

        $response = $this->postJson('/api/bookings/cancel', [
            'bookingId' => $bookingId
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success');

        // التحقق من تغيير الحالة إلى ملغي
        $this->assertDatabaseHas('bookings', [
            'id' => $bookingId,
            'status' => 'cancelled'
        ]);

        // التحقق من استرجاع السعة
        $this->assertDatabaseHas('parkings', [
            'id' => $parkingId,
            'available_capacity' => 5
        ]);
    }

    public function test_cancel_actual_booking_refund_100_percent()
    {
        $driver = $this->createDriverAccount('driver@test.com', 'PLATE-111', 80.00); // الرصيد الحالي 80
        $parkingId = $this->createParking();

        // حجز فعلي يبدأ بعد 40 دقيقة (أكثر من 30 دقيقة -> استرجاع 100%)
        $startTime = now()->addMinutes(40);
        $endTime = now()->addMinutes(160); // ساعتان -> التكلفة 5 نقاط

        $bookingId = DB::table('bookings')->insertGetId([
            'user_id' => $driver->id,
            'parking_id' => $parkingId,
            'plate_number' => 'PLATE-111',
            'start_time' => $startTime,
            'end_time' => $endTime,
            'type' => 'actual',
            'cost' => 5.00,
            'status' => 'confirmed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->postJson('/api/bookings/cancel', [
            'bookingId' => $bookingId
        ]);

        $response->assertStatus(200);

        // رصيد المحفظة يجب أن يعود إلى 85 (80 + 5 مسترجعة)
        $this->assertDatabaseHas('wallets', [
            'user_id' => $driver->id,
            'balance' => 85.00
        ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $bookingId,
            'status' => 'cancelled',
            'refund_amount' => 5.00
        ]);
    }

    public function test_cancel_actual_booking_refund_50_percent()
    {
        $driver = $this->createDriverAccount('driver@test.com', 'PLATE-111', 80.00);
        $parkingId = $this->createParking();

        // حجز يبدأ بعد 15 دقيقة (أقل من 30 دقيقة -> استرجاع 50% فقط)
        $startTime = now()->addMinutes(15);
        $endTime = now()->addMinutes(135); // ساعتان -> التكلفة 5 نقاط

        $bookingId = DB::table('bookings')->insertGetId([
            'user_id' => $driver->id,
            'parking_id' => $parkingId,
            'plate_number' => 'PLATE-111',
            'start_time' => $startTime,
            'end_time' => $endTime,
            'type' => 'actual',
            'cost' => 5.00,
            'status' => 'confirmed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->postJson('/api/bookings/cancel', [
            'bookingId' => $bookingId
        ]);

        $response->assertStatus(200);

        // استرجاع 50% من الـ 5 نقاط = 2.5 نقطة. الرصيد الجديد = 80 + 2.5 = 82.5
        $this->assertDatabaseHas('wallets', [
            'user_id' => $driver->id,
            'balance' => 82.50
        ]);
    }

    public function test_cancel_actual_booking_fails_after_start_time()
    {
        $driver = $this->createDriverAccount();
        $parkingId = $this->createParking();

        // حجز فعلي بدأ قبل 5 دقائق
        $bookingId = DB::table('bookings')->insertGetId([
            'user_id' => $driver->id,
            'parking_id' => $parkingId,
            'plate_number' => 'PLATE-111',
            'start_time' => now()->subMinutes(5),
            'end_time' => now()->addMinutes(55),
            'type' => 'actual',
            'cost' => 2.50,
            'status' => 'confirmed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->postJson('/api/bookings/cancel', [
            'bookingId' => $bookingId
        ]);

        $response->assertStatus(403)
                 ->assertJsonPath('message', 'عذراً، لا يمكن إلغاء الحجز الفعلي بعد حلول موعد البداية.');
    }

    /*
    |--------------------------------------------------------------------------
    | اختبارات تغيير الموقف (Change Spot)
    |--------------------------------------------------------------------------
    */

    public function test_change_spot_success()
    {
        $driver = $this->createDriverAccount();
        $oldParkingId = $this->createParking('Old Spot', 5);
        $newParkingId = $this->createParking('New Spot', 3);

        $bookingId = DB::table('bookings')->insertGetId([
            'user_id' => $driver->id,
            'parking_id' => $oldParkingId,
            'plate_number' => 'PLATE-111',
            'start_time' => now()->addMinutes(10),
            'end_time' => now()->addMinutes(40),
            'type' => 'initial',
            'status' => 'confirmed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // تعيين السعة الافتراضية
        DB::table('parkings')->where('id', $oldParkingId)->decrement('available_capacity', 1);

        $response = $this->postJson('/api/bookings/change-spot', [
            'bookingId' => $bookingId,
            'newParkingId' => $newParkingId
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('message', 'تم تبديل الساحة بنجاح.');

        // التأكد من استعادة السعة للموقف القديم
        $this->assertDatabaseHas('parkings', [
            'id' => $oldParkingId,
            'available_capacity' => 5
        ]);

        // التأكد من استهلاك سعة من الموقف الجديد
        $this->assertDatabaseHas('parkings', [
            'id' => $newParkingId,
            'available_capacity' => 2
        ]);

        // التأكد من تبديل الموقف في حقل الحجز
        $this->assertDatabaseHas('bookings', [
            'id' => $bookingId,
            'parking_id' => $newParkingId
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | تنظيف الحجوزات المنتهية (Cleanup Expired Bookings)
    |--------------------------------------------------------------------------
    */

    public function test_cleanup_expired_bookings()
    {
        $driver = $this->createDriverAccount();
        $parkingId = $this->createParking('Parking', 5);

        // حجز مبدئي منتهٍ (منذ 10 دقائق)
        $bookingId = DB::table('bookings')->insertGetId([
            'user_id' => $driver->id,
            'parking_id' => $parkingId,
            'plate_number' => 'PLATE-111',
            'start_time' => now()->subMinutes(40),
            'end_time' => now()->subMinutes(10),
            'type' => 'initial',
            'status' => 'confirmed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('parkings')->where('id', $parkingId)->decrement('available_capacity', 1);

        $response = $this->postJson('/api/bookings/cleanup-expired');

        $response->assertStatus(200)
                 ->assertJsonFragment(['status' => 'success']);

        // الحجز يجب أن يصبح ملغياً
        $this->assertDatabaseHas('bookings', [
            'id' => $bookingId,
            'status' => 'cancelled'
        ]);

        // السعة يجب أن تعود للموقف
        $this->assertDatabaseHas('parkings', [
            'id' => $parkingId,
            'available_capacity' => 5
        ]);

        // الحدث تم إطلاقه
        Event::assertDispatched(BookingExpiredEvent::class);
    }

    /*
    |--------------------------------------------------------------------------
    | إجراءات الموظف الميداني (Employee Field Actions: Entry/Exit)
    |--------------------------------------------------------------------------
    */

    public function test_user_field_action_requires_employee_role()
    {
        $driver = $this->createDriverAccount(); // ليس موظفاً

        $response = $this->post('/field/user/action', [
            'plate_number' => 'PLATE-111',
            'action_type' => 'entry',
            'user_id' => $driver->id // معرف سائق عادي
        ]);

        $response->assertStatus(403)
                 ->assertJsonPath('message', 'هذا الحساب ليس موظفاً ميدانياً.');
    }

    public function test_user_field_action_entry_actual_booking()
    {
        $empData = $this->createEmployeeAccount();
        $parkingId = $this->createParking('Emp Parking', 10, $empData['employee_id']);

        $driver = $this->createDriverAccount();
        DB::table('bookings')->insert([
            'user_id' => $driver->id,
            'parking_id' => $parkingId,
            'plate_number' => 'PLATE-111',
            'start_time' => now(),
            'end_time' => now()->addHour(),
            'type' => 'actual',
            'status' => 'confirmed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->post('/field/user/action', [
            'plate_number' => 'PLATE-111',
            'action_type' => 'entry',
            'user_id' => $empData['account']->id
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('message', 'تم تأكيد الدخول الفعلي بنجاح فوراً.');

        // التحقق من تحديث حالة الحجز إلى نشط (active)
        $this->assertDatabaseHas('bookings', [
            'plate_number' => 'PLATE-111',
            'status' => 'active'
        ]);

        // التحقق من تسجيل العملية في سجل التدقيق المالي
        $this->assertDatabaseHas('activity_cash_audit_logs', [
            'employee_id' => $empData['employee_id'],
            'operation_type' => 'entry',
            'plate_number' => 'PLATE-111'
        ]);
    }

    public function test_user_field_action_entry_initial_booking_requires_time()
    {
        $empData = $this->createEmployeeAccount();
        $parkingId = $this->createParking('Emp Parking', 10, $empData['employee_id']);

        $driver = $this->createDriverAccount();
        DB::table('bookings')->insert([
            'user_id' => $driver->id,
            'parking_id' => $parkingId,
            'plate_number' => 'PLATE-111',
            'start_time' => now(),
            'end_time' => now()->addMinutes(30),
            'type' => 'initial',
            'status' => 'confirmed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->post('/field/user/action', [
            'plate_number' => 'PLATE-111',
            'action_type' => 'entry',
            'user_id' => $empData['account']->id
        ]);

        // يجب أن يطلب وقت الخروج المتوقع
        $response->assertStatus(200)
                 ->assertJsonPath('status', 'requires_time');
    }

    public function test_user_field_action_entry_initial_booking_with_time_success()
    {
        $empData = $this->createEmployeeAccount();
        $parkingId = $this->createParking('Emp Parking', 10, $empData['employee_id']);

        $driver = $this->createDriverAccount('driver@test.com', 'PLATE-111', 100.00);
        DB::table('bookings')->insert([
            'user_id' => $driver->id,
            'parking_id' => $parkingId,
            'plate_number' => 'PLATE-111',
            'start_time' => now(),
            'end_time' => now()->addMinutes(30),
            'type' => 'initial',
            'status' => 'confirmed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->post('/field/user/action', [
            'plate_number' => 'PLATE-111',
            'action_type' => 'entry',
            'user_id' => $empData['account']->id,
            'expected_exit_time' => now()->addHours(2)->format('H:i')
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('message', 'تم تأكيد الدخول المبدئي بنجاح. سيتم احتساب التكلفة الفعالية والعقوبات عند الخروج.');

        $this->assertDatabaseHas('bookings', [
            'plate_number' => 'PLATE-111',
            'status' => 'active'
        ]);
    }

    public function test_user_field_action_exit_without_delay()
    {
        $empData = $this->createEmployeeAccount();
        $parkingId = $this->createParking('Emp Parking', 10, $empData['employee_id']);

        $driver = $this->createDriverAccount('driver@test.com', 'PLATE-111', 100.00);
        // حجز نشط غير متأخر
        DB::table('bookings')->insert([
            'user_id' => $driver->id,
            'parking_id' => $parkingId,
            'plate_number' => 'PLATE-111',
            'start_time' => now()->subMinutes(10),
            'end_time' => now()->addMinutes(50),
            'type' => 'actual',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->post('/field/user/action', [
            'plate_number' => 'PLATE-111',
            'action_type' => 'exit',
            'user_id' => $empData['account']->id
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('message', 'تم خروج المشترك بنجاح. (مدفوع مسبقاً وبدون تأخير).');

        $this->assertDatabaseHas('bookings', [
            'plate_number' => 'PLATE-111',
            'status' => 'completed'
        ]);
    }

    public function test_user_field_action_exit_with_delay_penalty()
    {
        $empData = $this->createEmployeeAccount();
        $parkingId = $this->createParking('Emp Parking', 10, $empData['employee_id']);

        $driver = $this->createDriverAccount('driver@test.com', 'PLATE-111', 10.00); // رصيد 10 نقاط
        // حجز نشط وانتهى موعد خروجه منذ 45 دقيقة (تأخير 45 دقيقة -> عقوبة نقطتان)
        DB::table('bookings')->insert([
            'user_id' => $driver->id,
            'parking_id' => $parkingId,
            'plate_number' => 'PLATE-111',
            'start_time' => now()->subHour(),
            'end_time' => now()->subMinutes(45),
            'type' => 'actual',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->post('/field/user/action', [
            'plate_number' => 'PLATE-111',
            'action_type' => 'exit',
            'user_id' => $empData['account']->id
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('message', 'تم تسجيل خروج المشترك. تم خصم عقوبة تأخير بقيمة 2 نقطة من المحفظة لتجاوز الوقت المحدد.');

        // رصيد المحفظة ينخفض بنقطتين ليصبح 8
        $this->assertDatabaseHas('wallets', [
            'user_id' => $driver->id,
            'balance' => 8.00
        ]);

        // التحقق من إرسال بريد التنبيه بالتأخير
        Mail::assertSent(LateExitNotification::class, function ($mail) use ($driver) {
            return $mail->hasTo($driver->email) && $mail->penaltyPoints == 2;
        });
    }

    public function test_get_parking_capacity()
    {
        $empData = $this->createEmployeeAccount();
        $this->createParking('Parking Name', 7, $empData['employee_id']);

        $response = $this->get('/field/parking/capacity?user_id=' . $empData['account']->id);

        $response->assertStatus(200)
                 ->assertJson(['capacity' => 7]);
    }

    /*
    |--------------------------------------------------------------------------
    | سجل الحجوزات (Booking History)
    |--------------------------------------------------------------------------
    */

    public function test_get_booking_history()
    {
        $driver = $this->createDriverAccount();
        $parkingId = $this->createParking('Tripoli Square');

        DB::table('bookings')->insert([
            'user_id' => $driver->id,
            'parking_id' => $parkingId,
            'plate_number' => 'PLATE-111',
            'start_time' => now(),
            'end_time' => now()->addMinutes(30),
            'type' => 'initial',
            'status' => 'confirmed',
            'created_at' => Carbon::create(2026, 6, 15, 12, 0, 0),
            'updated_at' => Carbon::create(2026, 6, 15, 12, 0, 0),
        ]);

        // طلب السجل بفلتر الشهر والسنة
        $response = $this->getJson("/api/bookings/history?userId={$driver->id}&month=6&year=2026");

        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success')
                 ->assertJsonFragment(['parking_name' => 'Tripoli Square']);
    }

    public function test_get_recommended_spots_sorted_by_ratio()
    {
        DB::table('parkings')->insert([
            'name' => 'Low Ratio Spot',
            'location_park' => 'Loc 1',
            'total_capacity' => 10,
            'available_capacity' => 2, // ratio = 0.2
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('parkings')->insert([
            'name' => 'High Ratio Spot',
            'location_park' => 'Loc 2',
            'total_capacity' => 10,
            'available_capacity' => 8, // ratio = 0.8
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->getJson('/api/parkings/recommend?sort_by=ratio');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals('High Ratio Spot', $data[0]['name']);
    }

    public function test_get_recommended_spots_fallback_sorting_null_distance()
    {
        DB::table('parkings')->insert([
            'name' => 'Spot 1',
            'location_park' => 'Loc 1',
            'total_capacity' => 10,
            'available_capacity' => 5,
            'latitude' => null,
            'longitude' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('parkings')->insert([
            'name' => 'Spot 2',
            'location_park' => 'Loc 2',
            'total_capacity' => 10,
            'available_capacity' => 5,
            'latitude' => null,
            'longitude' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->getJson('/api/parkings/recommend?sort_by=distance');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(2, $data);
    }

    public function test_cancel_initial_booking_fails_after_end_time()
    {
        $driver = $this->createDriverAccount();
        $parkingId = $this->createParking('Parking', 5);

        // حجز مبدئي انتهى ميعاده (انتهت مهلة الـ 20/30 دقيقة)
        $bookingId = DB::table('bookings')->insertGetId([
            'user_id' => $driver->id,
            'parking_id' => $parkingId,
            'plate_number' => 'PLATE-111',
            'start_time' => now()->subMinutes(40),
            'end_time' => now()->subMinutes(10), // في الماضي
            'type' => 'initial',
            'status' => 'confirmed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->postJson('/api/bookings/cancel', [
            'bookingId' => $bookingId
        ]);

        $response->assertStatus(403)
                 ->assertJsonPath('message', 'عذراً، انتهت مهلة الحجز المبدئي (20 دقيقة).');
    }

    /*
    |--------------------------------------------------------------------------
    | اختبارات معالجة الاستثناءات لتغطية 100% (Exception Handling / Mocking)
    |--------------------------------------------------------------------------
    */

    public function test_get_spots_exception_handling()
    {
        $originalDB = app('db');
        DB::shouldReceive('connection')->zeroOrMoreTimes()->andReturnSelf();
        DB::shouldReceive('table')->zeroOrMoreTimes()->andThrow(new \Exception('Simulated database error'));

        $response = $this->getJson('/api/parkings/spots');

        $response->assertStatus(500)
                 ->assertJsonPath('status', 'error')
                 ->assertJsonPath('message', 'Simulated database error');

        app()->instance('db', $originalDB);
        DB::clearResolvedInstance('db');
    }

    public function test_get_recommended_spots_exception_handling()
    {
        $originalDB = app('db');
        DB::shouldReceive('connection')->zeroOrMoreTimes()->andReturnSelf();
        DB::shouldReceive('table')->zeroOrMoreTimes()->andThrow(new \Exception('Simulated database error'));

        $response = $this->getJson('/api/parkings/recommend');

        $response->assertStatus(500)
                 ->assertJsonPath('status', 'error');

        app()->instance('db', $originalDB);
        DB::clearResolvedInstance('db');
    }

    public function test_get_active_booking_exception_handling()
    {
        $mock = \Mockery::mock(\App\Repositories\BookingRepositoryInterface::class);
        $mock->shouldReceive('getActiveBookingForUser')
             ->once()
             ->andThrow(new \Exception('Repo error'));

        $this->app->instance(\App\Repositories\BookingRepositoryInterface::class, $mock);

        $response = $this->getJson('/api/bookings/active?userId=1');

        $response->assertStatus(500)
                 ->assertJsonPath('status', 'error');
    }

    public function test_create_booking_exception_handling()
    {
        $mock = \Mockery::mock(\App\Repositories\BookingRepositoryInterface::class);
        $mock->shouldReceive('hasActiveBookingForUser')
             ->once()
             ->andThrow(new \Exception('Repo error'));

        $this->app->instance(\App\Repositories\BookingRepositoryInterface::class, $mock);

        $response = $this->postJson('/api/bookings/create', [
            'userId' => 1,
            'parkingId' => 1,
            'bookingType' => 'initial'
        ]);

        $response->assertStatus(500)
                 ->assertJsonPath('status', 'error');
    }

    public function test_cancel_booking_exception_handling()
    {
        $mock = \Mockery::mock(\App\Repositories\BookingRepositoryInterface::class);
        $mock->shouldReceive('getById')
             ->once()
             ->andThrow(new \Exception('Repo error'));

        $this->app->instance(\App\Repositories\BookingRepositoryInterface::class, $mock);

        $response = $this->postJson('/api/bookings/cancel', [
            'bookingId' => 1
        ]);

        $response->assertStatus(500)
                 ->assertJsonPath('status', 'error');
    }

    public function test_change_spot_exception_handling()
    {
        $parkingId = $this->createParking();

        $mock = \Mockery::mock(\App\Repositories\BookingRepositoryInterface::class);
        $mock->shouldReceive('getById')
             ->once()
             ->andThrow(new \Exception('Repo error'));

        $this->app->instance(\App\Repositories\BookingRepositoryInterface::class, $mock);

        $response = $this->postJson('/api/bookings/change-spot', [
            'bookingId' => 1,
            'newParkingId' => $parkingId
        ]);

        $response->assertStatus(500)
                 ->assertJsonPath('status', 'error');
    }

    public function test_cleanup_expired_bookings_exception_handling()
    {
        $mock = \Mockery::mock(\App\Repositories\BookingRepositoryInterface::class);
        $mock->shouldReceive('getExpiredInitialBookings')
             ->once()
             ->andThrow(new \Exception('Repo error'));

        $this->app->instance(\App\Repositories\BookingRepositoryInterface::class, $mock);

        $response = $this->postJson('/api/bookings/cleanup-expired');

        $response->assertStatus(500)
                 ->assertJsonPath('status', 'error');
    }

    public function test_user_field_action_exception_handling()
    {
        $empData = $this->createEmployeeAccount();
        $this->createParking('P', 5, $empData['employee_id']);

        $mock = \Mockery::mock(\App\Repositories\BookingRepositoryInterface::class);
        $mock->shouldReceive('getActiveBookingByPlate')
             ->andThrow(new \Exception('Repo error'));

        $this->app->instance(\App\Repositories\BookingRepositoryInterface::class, $mock);

        $response = $this->post('/field/user/action', [
            'plate_number' => 'PLATE-111',
            'action_type' => 'entry',
            'user_id' => $empData['account']->id
        ]);

        $response->assertStatus(500)
                 ->assertJsonPath('status', 'error');
    }

    public function test_get_booking_history_exception_handling()
    {
        $response = $this->getJson('/api/bookings/history');

        $response->assertStatus(500)
                 ->assertJsonPath('status', 'error');
    }

    /*
    |--------------------------------------------------------------------------
    | حالات الاختبار الإضافية للوصول لتغطية 100%
    |--------------------------------------------------------------------------
    */

    public function test_get_recommended_spots_wsm_null_coords()
    {
        // ساحة بدون خط عرض وطول
        DB::table('parkings')->insert([
            'name' => 'No Coordinates Spot',
            'location_park' => 'Loc 1',
            'total_capacity' => 10,
            'available_capacity' => 8,
            'latitude' => null,
            'longitude' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->getJson('/api/parkings/recommend?latitude=32.895456&longitude=13.180324&sort_by=wsm');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals('No Coordinates Spot', $data[0]['name']);
        $this->assertEquals(99999, $data[0]['distance_km']);
        $this->assertEquals(0.4, $data[0]['wsm_score']);
    }

    public function test_get_recommended_spots_wsm_zero_capacity()
    {
        // ساحة بسعة كلية 0
        DB::table('parkings')->insert([
            'name' => 'Zero Capacity WSM',
            'location_park' => 'Loc 1',
            'total_capacity' => 0,
            'available_capacity' => 0,
            'latitude' => 32.895456,
            'longitude' => 13.180324,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->getJson('/api/parkings/recommend?latitude=32.895456&longitude=13.180324&sort_by=wsm');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals('Zero Capacity WSM', $data[0]['name']);
    }

    public function test_get_recommended_spots_ratio_zero_capacity()
    {
        // ساحة بسعة كلية 0 للفرز بالنسبة
        DB::table('parkings')->insert([
            'name' => 'Zero Capacity Ratio',
            'location_park' => 'Loc 1',
            'total_capacity' => 0,
            'available_capacity' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->getJson('/api/parkings/recommend?sort_by=ratio');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals('Zero Capacity Ratio', $data[0]['name']);
    }

    public function test_create_booking_driver_profile_not_found()
    {
        // حساب سائق بدون سيرة ذاتية في جدول users
        $account = Account::create([
            'name' => 'No Profile Driver',
            'email' => 'noprofile@test.com',
            'phone' => '0912345688',
            'password' => bcrypt('secret123'),
            'role' => 'user',
        ]);

        // حساب آخر لإرضاء القيد الأجنبي في جدول users
        $otherAccount = Account::create([
            'name' => 'Other Driver',
            'email' => 'otherdriver@test.com',
            'phone' => '0912345600',
            'password' => bcrypt('secret123'),
            'role' => 'user',
        ]);

        // إدراج سجل في جدول users بـ id المطابق للحساب ولكن بـ account_id المطابق للحساب الآخر
        DB::table('users')->insert([
            'id' => $account->id,
            'account_id' => $otherAccount->id,
            'plate_number' => 'PLATE-999',
            'status' => 'active',
            'fake_booking_count' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $parkingId = $this->createParking('P1', 5);

        $response = $this->postJson('/api/bookings/create', [
            'userId' => $account->id,
            'parkingId' => $parkingId,
            'bookingType' => 'initial'
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('bookings', [
            'user_id' => $account->id,
            'plate_number' => 'غير محدد'
        ]);
    }

    public function test_create_actual_booking_short_duration()
    {
        $driver = $this->createDriverAccount('driver@test.com', 'PLATE-111', 100.00);
        $parkingId = $this->createParking('My Parking', 5);

        $startTime = now()->addHour();
        $endTime = now()->addHour()->addMinutes(30); // مدة 30 دقيقة -> فرق الساعات 0 -> يتم تعيينها كحد أدنى 1 ساعة

        $response = $this->postJson('/api/bookings/create', [
            'userId' => $driver->id,
            'parkingId' => $parkingId,
            'bookingType' => 'actual',
            'startTime' => $startTime->toDateTimeString(),
            'endTime' => $endTime->toDateTimeString(),
        ]);

        $response->assertStatus(201);
        // التكلفة يجب أن تكون لـ 1 ساعة = 2.5
        $this->assertDatabaseHas('bookings', [
            'user_id' => $driver->id,
            'cost' => 2.50
        ]);
    }

    public function test_change_spot_fails_actual_after_start_time()
    {
        $driver = $this->createDriverAccount();
        $oldParkingId = $this->createParking('Old Spot', 5);
        $newParkingId = $this->createParking('New Spot', 3);

        // حجز فعلي بدأ قبل دقيقتين
        $bookingId = DB::table('bookings')->insertGetId([
            'user_id' => $driver->id,
            'parking_id' => $oldParkingId,
            'plate_number' => 'PLATE-111',
            'start_time' => now()->subMinutes(2),
            'end_time' => now()->addHour(),
            'type' => 'actual',
            'status' => 'confirmed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->postJson('/api/bookings/change-spot', [
            'bookingId' => $bookingId,
            'newParkingId' => $newParkingId
        ]);

        $response->assertStatus(403)
                 ->assertJsonPath('message', 'لا يمكن تغيير الموقف بعد بدء وقت الحجز الفعلي.');
    }

    public function test_change_spot_fails_initial_after_end_time()
    {
        $driver = $this->createDriverAccount();
        $oldParkingId = $this->createParking('Old Spot', 5);
        $newParkingId = $this->createParking('New Spot', 3);

        // حجز مبدئي انتهى ميعاده
        $bookingId = DB::table('bookings')->insertGetId([
            'user_id' => $driver->id,
            'parking_id' => $oldParkingId,
            'plate_number' => 'PLATE-111',
            'start_time' => now()->subMinutes(40),
            'end_time' => now()->subMinutes(10),
            'type' => 'initial',
            'status' => 'confirmed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->postJson('/api/bookings/change-spot', [
            'bookingId' => $bookingId,
            'newParkingId' => $newParkingId
        ]);

        $response->assertStatus(403)
                 ->assertJsonPath('message', 'لا يمكن تغيير الموقف لأن مهلة الحجز المبدئي قد انتهت.');
    }

    public function test_change_spot_fails_parking_full()
    {
        $driver = $this->createDriverAccount();
        $oldParkingId = $this->createParking('Old Spot', 5);
        $newParkingId = $this->createParking('New Spot', 0); // سعة 0 (ممتلئ)

        $bookingId = DB::table('bookings')->insertGetId([
            'user_id' => $driver->id,
            'parking_id' => $oldParkingId,
            'plate_number' => 'PLATE-111',
            'start_time' => now()->addMinutes(10),
            'end_time' => now()->addMinutes(40),
            'type' => 'initial',
            'status' => 'confirmed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->postJson('/api/bookings/change-spot', [
            'bookingId' => $bookingId,
            'newParkingId' => $newParkingId
        ]);

        $response->assertStatus(400)
                 ->assertJsonPath('message', 'عذراً، الساحة الجديدة ممتلئة بالكامل.');
    }

    public function test_user_field_action_fails_no_parking_assigned()
    {
        $empData = $this->createEmployeeAccount(); // موظف بدون ساحة مخصصة له

        $response = $this->post('/field/user/action', [
            'plate_number' => 'PLATE-111',
            'action_type' => 'entry',
            'user_id' => $empData['account']->id
        ]);

        $response->assertStatus(404)
                 ->assertJsonPath('message', 'لا توجد ساحة معينة لك.');
    }

    public function test_user_field_action_entry_parking_full()
    {
        $empData = $this->createEmployeeAccount();
        $parkingId = $this->createParking('Parking Name', 0, $empData['employee_id']); // سعة 0 (ممتلئ)

        $response = $this->post('/field/user/action', [
            'plate_number' => 'PLATE-111',
            'action_type' => 'entry',
            'user_id' => $empData['account']->id
        ]);

        $response->assertStatus(400)
                 ->assertJsonPath('message', 'الموقف ممتلئ!');
    }

    public function test_user_field_action_entry_already_inside()
    {
        $empData = $this->createEmployeeAccount();
        $parkingId = $this->createParking('Parking Name', 5, $empData['employee_id']);

        $driver = $this->createDriverAccount();

        // حجز نشط (بالفعل داخل الموقف)
        DB::table('bookings')->insert([
            'user_id' => $driver->id,
            'parking_id' => $parkingId,
            'plate_number' => 'PLATE-111',
            'start_time' => now()->subMinutes(10),
            'end_time' => now()->addMinutes(50),
            'type' => 'actual',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->post('/field/user/action', [
            'plate_number' => 'PLATE-111',
            'action_type' => 'entry',
            'user_id' => $empData['account']->id
        ]);

        $response->assertStatus(400)
                 ->assertJsonPath('message', 'السيارة موجودة بالفعل.');
    }

    public function test_user_field_action_entry_no_booking()
    {
        $empData = $this->createEmployeeAccount();
        $parkingId = $this->createParking('Parking Name', 5, $empData['employee_id']);

        $response = $this->post('/field/user/action', [
            'plate_number' => 'PLATE-999', // لوحة بدون أي حجز
            'action_type' => 'entry',
            'user_id' => $empData['account']->id
        ]);

        $response->assertStatus(404)
                 ->assertJsonPath('message', 'لا يوجد حجز مسبق مؤكد لهذه اللوحة.');
    }

    public function test_user_field_action_entry_initial_time_in_past()
    {
        $empData = $this->createEmployeeAccount();
        $parkingId = $this->createParking('Parking Name', 5, $empData['employee_id']);

        $driver = $this->createDriverAccount('driver@test.com', 'PLATE-111', 100.00);

        DB::table('bookings')->insert([
            'user_id' => $driver->id,
            'parking_id' => $parkingId,
            'plate_number' => 'PLATE-111',
            'start_time' => now(),
            'end_time' => now()->addMinutes(30),
            'type' => 'initial',
            'status' => 'confirmed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // نمرر وقت خروج متوقع في الماضي (مثلاً قبل ساعة)
        $pastTimeStr = now()->subHour()->format('H:i');

        $response = $this->post('/field/user/action', [
            'plate_number' => 'PLATE-111',
            'action_type' => 'entry',
            'user_id' => $empData['account']->id,
            'expected_exit_time' => $pastTimeStr
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success');

        // يجب أن يضاف يوم كامل لوقت الخروج المخزن في قاعدة البيانات
        $updatedBooking = DB::table('bookings')->where('plate_number', 'PLATE-111')->first();
        $expectedExitTime = Carbon::createFromFormat('H:i', $pastTimeStr);
        if ($expectedExitTime->isPast()) {
            $expectedExitTime->addDay();
        }
        $this->assertEquals(Carbon::parse($updatedBooking->end_time)->toDateTimeString(), $expectedExitTime->toDateTimeString());
    }

    public function test_user_field_action_entry_initial_insufficient_wallet()
    {
        $empData = $this->createEmployeeAccount();
        $parkingId = $this->createParking('Parking Name', 5, $empData['employee_id']);

        $driver = $this->createDriverAccount('driver@test.com', 'PLATE-111', 2.00); // رصيد غير كافٍ (2 نقطة فقط)

        DB::table('bookings')->insert([
            'user_id' => $driver->id,
            'parking_id' => $parkingId,
            'plate_number' => 'PLATE-111',
            'start_time' => now(),
            'end_time' => now()->addMinutes(30),
            'type' => 'initial',
            'status' => 'confirmed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->post('/field/user/action', [
            'plate_number' => 'PLATE-111',
            'action_type' => 'entry',
            'user_id' => $empData['account']->id,
            'expected_exit_time' => now()->addHours(2)->format('H:i') // مدة ساعتين -> تكلفة 5 نقاط
        ]);

        $response->assertStatus(400)
                 ->assertJsonPath('message', 'رصيد غير كافٍ! (المطلوب: 5)');
    }

    public function test_user_field_action_exit_car_not_in_parking()
    {
        $empData = $this->createEmployeeAccount();
        $parkingId = $this->createParking('Parking Name', 5, $empData['employee_id']);

        $response = $this->post('/field/user/action', [
            'plate_number' => 'PLATE-999',
            'action_type' => 'exit',
            'user_id' => $empData['account']->id
        ]);

        $response->assertStatus(404)
                 ->assertJsonPath('message', 'السيارة غير موجودة بالموقف.');
    }

    public function test_user_field_action_exit_mail_sending_fails()
    {
        $empData = $this->createEmployeeAccount();
        $parkingId = $this->createParking('Emp Parking', 10, $empData['employee_id']);

        $driver = $this->createDriverAccount('driver@test.com', 'PLATE-111', 10.00);

        // حجز نشط متأخر (منذ 45 دقيقة)
        DB::table('bookings')->insert([
            'user_id' => $driver->id,
            'parking_id' => $parkingId,
            'plate_number' => 'PLATE-111',
            'start_time' => now()->subHour(),
            'end_time' => now()->subMinutes(45),
            'type' => 'actual',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // محاكاة إطلاق استثناء عند محاولة إرسال الإيميل
        Mail::shouldReceive('to')->andThrow(new \Exception('SMTP connection failed'));

        $response = $this->post('/field/user/action', [
            'plate_number' => 'PLATE-111',
            'action_type' => 'exit',
            'user_id' => $empData['account']->id
        ]);

        // يجب أن تنجح العملية ويتم تسجيل الخروج بنجاح بالرغم من فشل إرسال البريد الإلكتروني
        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('bookings', [
            'plate_number' => 'PLATE-111',
            'status' => 'completed'
        ]);
    }

    public function test_get_parking_capacity_employee_not_found()
    {
        // مستخدم ليس موظفاً
        $response = $this->get('/field/parking/capacity?user_id=99999');

        $response->assertStatus(200)
                 ->assertJson(['capacity' => 0]);
    }

    public function test_get_parking_capacity_parking_not_found()
    {
        $empData = $this->createEmployeeAccount(); // موظف بدون موقف مخصص له

        $response = $this->get('/field/parking/capacity?user_id=' . $empData['account']->id);

        $response->assertStatus(200)
                 ->assertJson(['capacity' => 0]);
    }

    public function test_get_booking_history_no_filters()
    {
        $driver = $this->createDriverAccount();
        $parkingId = $this->createParking('Tripoli Square');

        DB::table('bookings')->insert([
            'user_id' => $driver->id,
            'parking_id' => $parkingId,
            'plate_number' => 'PLATE-111',
            'start_time' => now(),
            'end_time' => now()->addMinutes(30),
            'type' => 'initial',
            'status' => 'confirmed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // طلب السجل بدون تمرير فلاتر الشهر والسنة
        $response = $this->getJson("/api/bookings/history?userId={$driver->id}");

        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success')
                 ->assertJsonFragment(['parking_name' => 'Tripoli Square']);
    }

    public function test_user_field_action_exit_initial_booking_with_delay()
    {
        $empData = $this->createEmployeeAccount();
        $parkingId = $this->createParking('Emp Parking', 10, $empData['employee_id']);

        $driver = $this->createDriverAccount('driver@test.com', 'PLATE-111', 100.00);
        // حجز نشط مبدئي انتهى ميعاد خروجه المتوقع (end_time) منذ 45 دقيقة (تأخير 45 دقيقة -> عقوبة 2 نقطة)
        // ووقت الدخول هو start_time منذ ساعة (ساعة واحدة فعلية -> تكلفة 2.5 نقاط)
        // التكلفة الكلية = 2.5 + 2 = 4.5 نقاط
        DB::table('bookings')->insert([
            'user_id' => $driver->id,
            'parking_id' => $parkingId,
            'plate_number' => 'PLATE-111',
            'start_time' => now()->subMinutes(50),
            'end_time' => now()->subMinutes(45),
            'type' => 'initial',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->post('/field/user/action', [
            'plate_number' => 'PLATE-111',
            'action_type' => 'exit',
            'user_id' => $empData['account']->id
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success')
                 ->assertJsonPath('message', 'تم تسجيل الخروج وخصم 4.5 من المحفظة. (شاملة عقوبة تأخير: 2 نقطة).');

        // الرصيد المتبقي = 100 - 4.5 = 95.50
        $this->assertDatabaseHas('wallets', [
            'user_id' => $driver->id,
            'balance' => 95.50
        ]);
    }

    public function test_cancel_booking_fails_already_cancelled_or_not_found()
    {
        $driver = $this->createDriverAccount();
        $parkingId = $this->createParking('Parking', 5);

        // 1. حجز حالته ملغية بالفعل
        $bookingId = DB::table('bookings')->insertGetId([
            'user_id' => $driver->id,
            'parking_id' => $parkingId,
            'plate_number' => 'PLATE-111',
            'start_time' => now(),
            'end_time' => now()->addMinutes(30),
            'type' => 'initial',
            'status' => 'cancelled', // ملغي مسبقاً
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->postJson('/api/bookings/cancel', [
            'bookingId' => $bookingId
        ]);

        $response->assertStatus(400)
                 ->assertJsonPath('message', 'الحجز غير موجود أو ملغي مسبقاً.');

        // 2. حجز غير موجود بالأساس (معرف خاطئ)
        $response2 = $this->postJson('/api/bookings/cancel', [
            'bookingId' => 99999
        ]);

        $response2->assertStatus(400)
                  ->assertJsonPath('message', 'الحجز غير موجود أو ملغي مسبقاً.');
    }
}
