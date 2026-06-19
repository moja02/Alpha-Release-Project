<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Carbon\Carbon;

class RechargeControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    protected function tearDown(): void
    {
        while (DB::transactionLevel() > 0) {
            DB::rollBack();
        }
        parent::tearDown();
    }

    /**
     * Helper to create a driver account along with its user record and initial wallet.
     */
    protected function createDriver(array $attributes = []): Account
    {
        $account = Account::create(array_merge([
            'name' => 'Driver User',
            'email' => 'driver@test.com',
            'phone' => '123456789',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ], $attributes));

        DB::table('users')->insert([
            'id' => $account->id,
            'account_id' => $account->id,
            'plate_number' => 'OB-11111',
            'status' => 'active',
            'fake_booking_count' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $account;
    }

    /**
     * Helper to create an employee account and profile.
     */
    protected function createEmployee(array $attributes = []): array
    {
        $account = Account::create(array_merge([
            'name' => 'Employee User',
            'email' => 'employee@test.com',
            'phone' => '123456789',
            'password' => Hash::make('password123'),
            'role' => 'employee',
        ], $attributes));

        $employeeId = DB::table('employees')->insertGetId([
            'account_id' => $account->id,
            'bank_account_number' => 'LY99999',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'account' => $account,
            'employee_id' => $employeeId,
        ];
    }

    /**
     * Helper to create a parking yard assigned to an employee.
     */
    protected function createParking(int $employeeId, array $attributes = []): int
    {
        return DB::table('parkings')->insertGetId(array_merge([
            'name' => 'Al-Ahly Yard',
            'location_park' => 'Benghazi',
            'total_capacity' => 20,
            'available_capacity' => 20,
            'employee_id' => $employeeId,
            'created_at' => now(),
            'updated_at' => now(),
        ], $attributes));
    }

    /*
    |--------------------------------------------------------------------------
    | getPendingRecharges Tests
    |--------------------------------------------------------------------------
    */

    public function test_get_pending_recharges_success()
    {
        $driver = $this->createDriver(['email' => 'd1@test.com']);
        $empDetails = $this->createEmployee(['email' => 'e1@test.com']);
        $parkingId = $this->createParking($empDetails['employee_id']);

        // Insert pending recharge request linked to this parking lot
        $requestId = DB::table('recharge_requests')->insertGetId([
            'user_id' => $driver->id,
            'parking_id' => $parkingId,
            'requested_points' => 100,
            'receipt_file' => 'receipts/receipt.jpg',
            'status' => 'Pending',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Request with employee ID
        $response = $this->getJson("/api/recharges/pending?employeeId=" . $empDetails['employee_id']);

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.id', $requestId);
        $response->assertJsonPath('data.0.user_name', 'Driver User');
        $response->assertJsonPath('data.0.parking_name', 'Al-Ahly Yard');
    }

    public function test_get_pending_recharges_validation_fails()
    {
        $response = $this->getJson("/api/recharges/pending"); // Missing employeeId

        $response->assertStatus(500); // Thrown due to ValidationException caught and returned as 500
        $response->assertJsonPath('status', 'error');
    }

    /*
    |--------------------------------------------------------------------------
    | directRecharge Tests
    |--------------------------------------------------------------------------
    */

    public function test_direct_recharge_success_creates_wallet()
    {
        $driver = $this->createDriver(['email' => 'd2@test.com']);
        $empDetails = $this->createEmployee(['email' => 'e2@test.com']);
        $parkingId = $this->createParking($empDetails['employee_id']);

        // Note: wallet is NOT created yet for this driver.
        // We will execute a direct recharge which should insert the wallet.
        $this->actingAs($empDetails['account']);

        $response = $this->postJson("/api/recharges/direct", [
            'userId' => $driver->id,
            'amount' => 50,
            'employee_id' => $empDetails['account']->id
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');
        $response->assertJsonPath('message', 'تم شحن المحفظة بنجاح.');

        // Verify wallet balance is 50
        $this->assertDatabaseHas('wallets', [
            'user_id' => $driver->id,
            'balance' => 50.00
        ]);

        // Verify driver notification
        $this->assertDatabaseHas('notifications', [
            'user_id' => $driver->id,
            'type' => 'Direct_Recharge',
            'message' => 'تم شحن محفظتك بـ 50 نقطة مباشرة من قبل الإدارة.'
        ]);

        // Verify cash audit log
        $this->assertDatabaseHas('activity_cash_audit_logs', [
            'employee_id' => $empDetails['employee_id'],
            'parking_id' => $parkingId,
            'operation_type' => 'recharge',
            'cash_value' => 50.00,
            'driver_account_id' => $driver->id,
            'plate_number' => 'OB-11111'
        ]);
    }

    public function test_direct_recharge_success_increments_wallet()
    {
        $driver = $this->createDriver(['email' => 'd3@test.com']);
        $empDetails = $this->createEmployee(['email' => 'e3@test.com']);
        $parkingId = $this->createParking($empDetails['employee_id']);

        // Insert initial wallet balance
        DB::table('wallets')->insert([
            'user_id' => $driver->id,
            'balance' => 30.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($empDetails['account']);

        $response = $this->postJson("/api/recharges/direct", [
            'userId' => $driver->id,
            'amount' => 70,
            'employee_id' => $empDetails['account']->id
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');

        // Balance should be 30 + 70 = 100
        $this->assertDatabaseHas('wallets', [
            'user_id' => $driver->id,
            'balance' => 100.00
        ]);
    }

    public function test_direct_recharge_validation_fails()
    {
        $response = $this->postJson("/api/recharges/direct", [
            'userId' => 9999, // nonexistent account
            'amount' => -10
        ]);

        $response->assertStatus(500); // Fails validation constraints
        $response->assertJsonPath('status', 'error');
    }

    /*
    |--------------------------------------------------------------------------
    | verifyRequest Tests
    |--------------------------------------------------------------------------
    */

    public function test_verify_request_approve_success()
    {
        $driver = $this->createDriver(['email' => 'd4@test.com']);

        DB::table('wallets')->insert([
            'user_id' => $driver->id,
            'balance' => 10.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $requestId = DB::table('recharge_requests')->insertGetId([
            'user_id' => $driver->id,
            'parking_id' => null,
            'requested_points' => 200,
            'receipt_file' => 'receipts/rec.jpg',
            'status' => 'Pending',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $response = $this->postJson("/api/recharges/verify", [
            'requestId' => $requestId,
            'action' => 'approve'
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');

        // Verify request updated to Approved
        $this->assertDatabaseHas('recharge_requests', [
            'id' => $requestId,
            'status' => 'Approved'
        ]);

        // Balance updated from 10 to 10 + 200 = 210
        $this->assertDatabaseHas('wallets', [
            'user_id' => $driver->id,
            'balance' => 210.00
        ]);

        // Verify notification
        $this->assertDatabaseHas('notifications', [
            'user_id' => $driver->id,
            'type' => 'Recharge_Approved',
            'message' => 'تهانينا! تم اعتماد إيصال التحويل الخاص بك وإضافة 200 نقطة لمحفظتك.'
        ]);
    }

    public function test_verify_request_reject_success()
    {
        $driver = $this->createDriver(['email' => 'd5@test.com']);

        DB::table('wallets')->insert([
            'user_id' => $driver->id,
            'balance' => 15.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $requestId = DB::table('recharge_requests')->insertGetId([
            'user_id' => $driver->id,
            'parking_id' => null,
            'requested_points' => 150,
            'receipt_file' => 'receipts/rec.jpg',
            'status' => 'Pending',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $response = $this->postJson("/api/recharges/verify", [
            'requestId' => $requestId,
            'action' => 'reject',
            'rejectionReason' => 'الإيصال غير واضح'
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');

        // Status updated to Rejected
        $this->assertDatabaseHas('recharge_requests', [
            'id' => $requestId,
            'status' => 'Rejected'
        ]);

        // Balance remains unchanged (15)
        $this->assertDatabaseHas('wallets', [
            'user_id' => $driver->id,
            'balance' => 15.00
        ]);

        // Verify rejection notification
        $this->assertDatabaseHas('notifications', [
            'user_id' => $driver->id,
            'type' => 'Recharge_Rejected',
            'message' => 'عذراً، تم رفض طلب الشحن الخاص بك. السبب: الإيصال غير واضح'
        ]);
    }

    public function test_verify_request_cannot_be_processed_twice()
    {
        $driver = $this->createDriver(['email' => 'd6@test.com']);

        $requestId = DB::table('recharge_requests')->insertGetId([
            'user_id' => $driver->id,
            'parking_id' => null,
            'requested_points' => 150,
            'receipt_file' => 'receipts/rec.jpg',
            'status' => 'Approved', // Already Approved
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $response = $this->postJson("/api/recharges/verify", [
            'requestId' => $requestId,
            'action' => 'approve'
        ]);

        $response->assertStatus(400); // Bad Request because it's not pending
        $response->assertJsonPath('status', 'error');
        $response->assertJsonPath('message', 'هذا الطلب تمت معالجته مسبقاً.');
    }

    /*
    |--------------------------------------------------------------------------
    | getBalance Tests
    |--------------------------------------------------------------------------
    */

    public function test_get_balance_success()
    {
        $driver = $this->createDriver(['email' => 'd7@test.com']);

        DB::table('wallets')->insert([
            'user_id' => $driver->id,
            'balance' => 450.75,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->getJson("/api/wallet/balance?userId=" . $driver->id);

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');
        $response->assertJsonPath('balance', 450.75);
    }

    public function test_get_balance_validation_fails()
    {
        $response = $this->getJson("/api/wallet/balance"); // Missing userId

        $response->assertStatus(500);
        $response->assertJsonPath('status', 'error');
    }

    /*
    |--------------------------------------------------------------------------
    | submitRequest Tests
    |--------------------------------------------------------------------------
    */

    public function test_submit_request_success()
    {
        $driver = $this->createDriver(['email' => 'd8@test.com']);
        $empDetails = $this->createEmployee(['email' => 'e8@test.com']);
        $parkingId = $this->createParking($empDetails['employee_id']);

        $file = UploadedFile::fake()->image('receipt.png');

        $response = $this->postJson("/api/recharges/request", [
            'userId' => $driver->id,
            'parkingId' => $parkingId,
            'amount' => 80,
            'receipt' => $file
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('status', 'success');

        // Check file exists in faked storage public directory
        $uploadedRequest = DB::table('recharge_requests')->where('user_id', $driver->id)->first();
        $this->assertNotNull($uploadedRequest);
        Storage::disk('public')->assertExists($uploadedRequest->receipt_file);

        // Check DB entry
        $this->assertDatabaseHas('recharge_requests', [
            'user_id' => $driver->id,
            'parking_id' => $parkingId,
            'requested_points' => 80,
            'status' => 'Pending'
        ]);
    }

    public function test_submit_request_validation_fails()
    {
        $driver = $this->createDriver(['email' => 'd9@test.com']);

        // Submit with amount less than 5 points
        $response = $this->postJson("/api/recharges/request", [
            'userId' => $driver->id,
            'parkingId' => 1,
            'amount' => 3, // Min is 5
            'receipt' => UploadedFile::fake()->image('receipt.png')
        ]);

        $response->assertStatus(500);
        $response->assertJsonPath('status', 'error');
    }

    /*
    |--------------------------------------------------------------------------
    | getUserRechargeRequests & getDirectRechargeInvoices
    |--------------------------------------------------------------------------
    */

    public function test_get_user_recharge_requests()
    {
        $driver = $this->createDriver(['email' => 'd10@test.com']);

        DB::table('recharge_requests')->insert([
            'user_id' => $driver->id,
            'parking_id' => null,
            'requested_points' => 25,
            'receipt_file' => 'receipts/test.jpg',
            'status' => 'Pending',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $response = $this->getJson("/api/recharges/user-requests?userId=" . $driver->id);

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.requested_points', 25);
    }

    public function test_get_direct_recharge_invoices()
    {
        $driver = $this->createDriver(['email' => 'd11@test.com']);
        $empDetails = $this->createEmployee(['email' => 'e11@test.com']);
        $parkingId = $this->createParking($empDetails['employee_id']);

        // Insert cash audit logs for direct recharge
        DB::table('activity_cash_audit_logs')->insert([
            'employee_id' => $empDetails['employee_id'],
            'parking_id' => $parkingId,
            'operation_type' => 'recharge',
            'plate_number' => 'OB-11111',
            'cash_value' => 60.00,
            'driver_account_id' => $driver->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->getJson("/api/recharges/invoices?userId=" . $driver->id);

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.cash_value', 60);
        $response->assertJsonPath('data.0.parking_name', 'Al-Ahly Yard');
        $response->assertJsonPath('data.0.employee_name', 'Employee User');
    }

    public function test_verify_request_approve_without_existing_wallet()
    {
        $driver = $this->createDriver(['email' => 'd12_no_wallet@test.com']);

        $this->assertDatabaseMissing('wallets', [
            'user_id' => $driver->id,
        ]);

        $requestId = DB::table('recharge_requests')->insertGetId([
            'user_id' => $driver->id,
            'parking_id' => null,
            'requested_points' => 300,
            'receipt_file' => 'receipts/rec_no_wallet.jpg',
            'status' => 'Pending',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $response = $this->postJson("/api/recharges/verify", [
            'requestId' => $requestId,
            'action' => 'approve'
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('wallets', [
            'user_id' => $driver->id,
            'balance' => 300.00
        ]);
    }

    public function test_verify_request_throws_exception()
    {
        $driver = $this->createDriver(['email' => 'd13_ex@test.com']);

        $requestId = DB::table('recharge_requests')->insertGetId([
            'user_id' => $driver->id,
            'parking_id' => null,
            'requested_points' => 100,
            'receipt_file' => 'receipts/rec_ex.jpg',
            'status' => 'Pending',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $db = DB::getFacadeRoot();
        $originalConnection = DB::connection();

        $mockConnection = \Mockery::mock($originalConnection)->makePartial();
        $mockConnection->shouldReceive('table')
            ->with('wallets')
            ->andThrow(new \Exception('Database failure during verification'));

        $ref = new \ReflectionProperty(get_class($db), 'connections');
        $ref->setAccessible(true);
        $connections = $ref->getValue($db);
        $connections['sqlite'] = $mockConnection;
        $ref->setValue($db, $connections);

        try {
            $response = $this->postJson("/api/recharges/verify", [
                'requestId' => $requestId,
                'action' => 'approve'
            ]);
        } finally {
            $connections['sqlite'] = $originalConnection;
            $ref->setValue($db, $connections);
        }

        $response->assertStatus(500);
        $response->assertJsonPath('status', 'error');
        $response->assertJsonPath('message', 'Database failure during verification');
    }

    public function test_get_user_recharge_requests_throws_exception()
    {
        $driver = $this->createDriver(['email' => 'd14_ex@test.com']);

        $db = DB::getFacadeRoot();
        $originalConnection = DB::connection();

        $mockConnection = \Mockery::mock($originalConnection)->makePartial();
        $mockConnection->shouldReceive('table')
            ->with('recharge_requests')
            ->andThrow(new \Exception('Database error fetching requests'));

        $ref = new \ReflectionProperty(get_class($db), 'connections');
        $ref->setAccessible(true);
        $connections = $ref->getValue($db);
        $connections['sqlite'] = $mockConnection;
        $ref->setValue($db, $connections);

        try {
            $response = $this->getJson("/api/recharges/user-requests?userId=" . $driver->id);
        } finally {
            $connections['sqlite'] = $originalConnection;
            $ref->setValue($db, $connections);
        }

        $response->assertStatus(500);
        $response->assertJsonPath('status', 'error');
        $response->assertJsonPath('message', 'Database error fetching requests');
    }

    public function test_get_direct_recharge_invoices_throws_exception()
    {
        $driver = $this->createDriver(['email' => 'd15_ex@test.com']);

        $db = DB::getFacadeRoot();
        $originalConnection = DB::connection();

        $mockConnection = \Mockery::mock($originalConnection)->makePartial();
        $mockConnection->shouldReceive('table')
            ->with('activity_cash_audit_logs')
            ->andThrow(new \Exception('Database error fetching invoices'));

        $ref = new \ReflectionProperty(get_class($db), 'connections');
        $ref->setAccessible(true);
        $connections = $ref->getValue($db);
        $connections['sqlite'] = $mockConnection;
        $ref->setValue($db, $connections);

        try {
            $response = $this->getJson("/api/recharges/invoices?userId=" . $driver->id);
        } finally {
            $connections['sqlite'] = $originalConnection;
            $ref->setValue($db, $connections);
        }

        $response->assertStatus(500);
        $response->assertJsonPath('status', 'error');
        $response->assertJsonPath('message', 'حدث خطأ أثناء جلب فواتير الشحن المباشر: Database error fetching invoices');
    }
}
