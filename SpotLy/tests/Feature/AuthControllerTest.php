<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\SpotlyNotificationMail;
use App\Models\Account;

class AuthControllerTest extends TestCase
{
    use DatabaseMigrations; // تصفير قاعدة البيانات بالهجرات لكل اختبار

    protected function setUp(): void
    {
        parent::setUp();
        // إعداد البريد الإلكتروني الوهمي لمنع الإرسال الحقيقي أثناء الاختبارات
        Mail::fake();
        // تفعيل الجلسة الافتراضية للطلبات تفادياً لأخطاء Session store
        $this->withSession([]);
    }

    /**
     * اختبار أخطاء التحقق من مدخلات تسجيل الدخول.
     */
    public function test_login_validation_errors()
    {
        $response = $this->postJson('/api/accounts/login', []);

        $response->assertStatus(422) // Unprocessable Entity
                 ->assertJsonValidationErrors(['email', 'password']);
    }

    /**
     * اختبار تسجيل الدخول ببيانات خاطئة.
     */
    public function test_login_with_invalid_credentials()
    {
        $response = $this->postJson('/api/accounts/login', [
            'email' => 'unknown@test.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertStatus(401)
                 ->assertJsonPath('status', 'error')
                 ->assertJsonPath('message', 'Invalid email or password.');
    }

    /**
     * اختبار تسجيل دخول ناجح لسائق (Role: user) وحالته نشطة.
     */
    public function test_login_success_for_driver()
    {
        $accountId = DB::table('accounts')->insertGetId([
            'name' => 'Driver User',
            'email' => 'driver@test.com',
            'phone' => '0912345678',
            'password' => bcrypt('secret123'),
            'role' => 'user',
        ]);

        DB::table('users')->insert([
            'account_id' => $accountId,
            'plate_number' => 'PLATE-111',
            'status' => 'active'
        ]);

        $response = $this->postJson('/api/accounts/login', [
            'email' => 'driver@test.com',
            'password' => 'secret123'
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success')
                 ->assertJsonPath('redirect', '/user-dashboard')
                 ->assertJsonStructure(['token', 'user', 'accountData']);
    }

    /**
     * اختبار منع تسجيل دخول سائق محظور (Blocked User).
     */
    public function test_login_denied_for_blocked_driver()
    {
        $accountId = DB::table('accounts')->insertGetId([
            'name' => 'Blocked Driver',
            'email' => 'blocked_driver@test.com',
            'phone' => '0912345678',
            'password' => bcrypt('secret123'),
            'role' => 'user',
        ]);

        DB::table('users')->insert([
            'account_id' => $accountId,
            'plate_number' => 'PLATE-222',
            'status' => 'blocked'
        ]);

        $response = $this->postJson('/api/accounts/login', [
            'email' => 'blocked_driver@test.com',
            'password' => 'secret123'
        ]);

        $response->assertStatus(403)
                 ->assertJsonPath('status', 'error')
                 ->assertJsonPath('message', 'Access Denied: Your account is blocked.');
    }

    /**
     * اختبار تسجيل دخول ناجح لموظف (Role: employee).
     */
    public function test_login_success_for_employee()
    {
        $accountId = DB::table('accounts')->insertGetId([
            'name' => 'Employee User',
            'email' => 'employee@test.com',
            'phone' => '0912345678',
            'password' => bcrypt('secret123'),
            'role' => 'employee',
        ]);

        DB::table('employees')->insert([
            'account_id' => $accountId,
            'bank_account_number' => '123456789'
        ]);

        $response = $this->postJson('/api/accounts/login', [
            'email' => 'employee@test.com',
            'password' => 'secret123'
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success')
                 ->assertJsonPath('redirect', '/employee-dashboard');
    }

    /**
     * اختبار تسجيل دخول ناجح لمدير (Role: manager) وحالته نشطة.
     */
    public function test_login_success_for_manager()
    {
        $accountId = DB::table('accounts')->insertGetId([
            'name' => 'Manager User',
            'email' => 'manager@test.com',
            'phone' => '0912345678',
            'password' => bcrypt('secret123'),
            'role' => 'manager',
        ]);

        DB::table('managers')->insert([
            'account_id' => $accountId,
            'status' => 'active'
        ]);

        $response = $this->postJson('/api/accounts/login', [
            'email' => 'manager@test.com',
            'password' => 'secret123'
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success')
                 ->assertJsonPath('redirect', '/manager/dashboard');
    }

    /**
     * اختبار منع تسجيل دخول مدير محظور (Blocked Manager).
     */
    public function test_login_denied_for_blocked_manager()
    {
        $accountId = DB::table('accounts')->insertGetId([
            'name' => 'Blocked Manager',
            'email' => 'blocked_manager@test.com',
            'phone' => '0912345678',
            'password' => bcrypt('secret123'),
            'role' => 'manager',
        ]);

        DB::table('managers')->insert([
            'account_id' => $accountId,
            'status' => 'blocked'
        ]);

        $response = $this->postJson('/api/accounts/login', [
            'email' => 'blocked_manager@test.com',
            'password' => 'secret123'
        ]);

        $response->assertStatus(403)
                 ->assertJsonPath('status', 'error')
                 ->assertJsonPath('message', 'Access Denied: Your account is blocked.');
    }

    /**
     * اختبار تسجيل دخول ناجح لمطور (Role: developer).
     */
    public function test_login_success_for_developer()
    {
        DB::table('accounts')->insert([
            'name' => 'Developer User',
            'email' => 'developer@test.com',
            'phone' => '0912345678',
            'password' => bcrypt('secret123'),
            'role' => 'developer',
        ]);

        $response = $this->postJson('/api/accounts/login', [
            'email' => 'developer@test.com',
            'password' => 'secret123'
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success')
                 ->assertJsonPath('redirect', '/developer/dashboard');
    }

    /**
     * اختبار فشل إرسال رمز OTP لبريد غير مسجل.
     */
    public function test_send_otp_fails_for_nonexistent_email()
    {
        $response = $this->postJson('/api/auth/forgot-password/send-otp', [
            'email' => 'nonexistent@test.com'
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    /**
     * اختبار إرسال رمز OTP بنجاح وحفظه بالجدول وإرسال إيميل.
     */
    public function test_send_otp_successfully()
    {
        DB::table('accounts')->insert([
            'name' => 'User Test',
            'email' => 'user@test.com',
            'phone' => '0912345678',
            'password' => bcrypt('secret123'),
            'role' => 'user',
        ]);

        $response = $this->postJson('/api/auth/forgot-password/send-otp', [
            'email' => 'user@test.com'
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success');

        // التحقق من حفظ الرمز في قاعدة البيانات
        $this->assertDatabaseHas('otp_codes', [
            'email' => 'user@test.com'
        ]);

        // التحقق من إرسال الإيميل للمستخدم الحقيقي
        Mail::assertSent(SpotlyNotificationMail::class, function ($mail) {
            return $mail->hasTo('user@test.com') && str_contains($mail->mailDetails['title'], 'رمز إعادة تعيين');
        });
    }

    /**
     * اختبار فشل استعادة كلمة السر برمز OTP خاطئ.
     */
    public function test_reset_password_fails_with_invalid_otp()
    {
        DB::table('accounts')->insert([
            'name' => 'User Test',
            'email' => 'user@test.com',
            'phone' => '0912345678',
            'password' => bcrypt('old_password'),
            'role' => 'user',
        ]);

        // إدراج رمز صحيح في قاعدة البيانات
        DB::table('otp_codes')->insert([
            'email' => 'user@test.com',
            'otp_code' => bcrypt('123456'),
            'expires_at' => now()->addMinutes(15)
        ]);

        // محاولة إعادة التعيين برمز خاطئ (654321)
        $response = $this->postJson('/api/auth/forgot-password/reset', [
            'email' => 'user@test.com',
            'otpCode' => '654321', // خاطئ
            'newPassword' => 'new_secret123'
        ]);

        $response->assertStatus(400)
                 ->assertJsonPath('status', 'error')
                 ->assertJsonPath('message', 'رمز التحقق المدخل لا يطابق الرمز المرسل.');
    }

    /**
     * اختبار فشل استعادة كلمة السر برمز OTP منتهي الصلاحية.
     */
    public function test_reset_password_fails_with_expired_otp()
    {
        DB::table('accounts')->insert([
            'name' => 'User Test',
            'email' => 'user@test.com',
            'phone' => '0912345678',
            'password' => bcrypt('old_password'),
            'role' => 'user',
        ]);

        // رمز منتهي الصلاحية (منذ 5 دقائق)
        DB::table('otp_codes')->insert([
            'email' => 'user@test.com',
            'otp_code' => bcrypt('123456'),
            'expires_at' => now()->subMinutes(5)
        ]);

        $response = $this->postJson('/api/auth/forgot-password/reset', [
            'email' => 'user@test.com',
            'otpCode' => '123456',
            'newPassword' => 'new_secret123'
        ]);

        $response->assertStatus(400)
                 ->assertJsonPath('status', 'error')
                 ->assertJsonPath('message', 'رمز التحقق غير صحيح أو منتهي الصلاحية.');
    }

    /**
     * اختبار نجاح إعادة تعيين كلمة المرور بالكامل.
     */
    public function test_reset_password_successfully()
    {
        DB::table('accounts')->insert([
            'name' => 'User Test',
            'email' => 'user@test.com',
            'phone' => '0912345678',
            'password' => bcrypt('old_password'),
            'role' => 'user',
        ]);

        DB::table('otp_codes')->insert([
            'email' => 'user@test.com',
            'otp_code' => bcrypt('123456'),
            'expires_at' => now()->addMinutes(15)
        ]);

        $response = $this->postJson('/api/auth/forgot-password/reset', [
            'email' => 'user@test.com',
            'otpCode' => '123456',
            'newPassword' => 'new_secret123'
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success')
                 ->assertJsonPath('message', 'تم إعادة تعيين كلمة المرور بنجاح.');

        // 1. التحقق من مسح رمز الـ OTP بعد الاستخدام
        $this->assertDatabaseMissing('otp_codes', [
            'email' => 'user@test.com'
        ]);

        // 2. التحقق من تحديث كلمة المرور في جدول الحسابات
        $updatedAccount = Account::where('email', 'user@test.com')->first();
        $this->assertTrue(Hash::check('new_secret123', $updatedAccount->password));

        // 3. التحقق من إرسال إيميل التأكيد بنجاح التغيير
        Mail::assertSent(SpotlyNotificationMail::class, function ($mail) {
            return $mail->hasTo('user@test.com') && str_contains($mail->mailDetails['title'], 'تغيير كلمة السر');
        });
    }

    /**
     * اختبار معالجة الاستثناءات العامة في تسجيل الدخول.
     */
    public function test_login_throws_exception()
    {
        // توليد كلمة مرور مشفرة مسبقاً لمنع استدعاء HashManager أثناء الإدراج
        $passwordHash = bcrypt('secret123');

        // إدراج حساب
        DB::table('accounts')->insert([
            'name' => 'Test User',
            'email' => 'exception@test.com',
            'phone' => '0912345678',
            'password' => $passwordHash,
            'role' => 'user',
        ]);

        // محاكاة رمي استثناء عند مطابقة كلمة المرور لتفعيل catch (\Exception)
        Hash::shouldReceive('check')
            ->andThrow(new \Exception('Test generic login exception'));

        $response = $this->postJson('/api/accounts/login', [
            'email' => 'exception@test.com',
            'password' => 'secret123'
        ]);

        $response->assertStatus(500)
                 ->assertJsonPath('status', 'error')
                 ->assertJsonPath('message', 'Test generic login exception');
    }

    /**
     * اختبار نجاح تسجيل الدخول مع تفعيل الجلسة وتجديدها (Session Regeneration).
     */
    public function test_login_success_with_session_regeneration()
    {
        $accountId = DB::table('accounts')->insertGetId([
            'name' => 'Session User',
            'email' => 'session@test.com',
            'phone' => '0912345678',
            'password' => bcrypt('secret123'),
            'role' => 'user',
        ]);

        DB::table('users')->insert([
            'account_id' => $accountId,
            'plate_number' => 'PLATE-333',
            'status' => 'active'
        ]);

        // نرسل الطلب إلى مسار الـ Web ليكون هناك جلسة مرافقة للطلب
        $response = $this->post('/web-login', [
            'email' => 'session@test.com',
            'password' => 'secret123'
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success');
    }

    /**
     * اختبار معالجة الاستثناءات العامة في إرسال الرمز.
     */
    public function test_send_otp_throws_exception()
    {
        DB::table('accounts')->insert([
            'name' => 'User Test',
            'email' => 'user@test.com',
            'phone' => '0912345678',
            'password' => bcrypt('secret123'),
            'role' => 'user',
        ]);

        // نقوم بمحاكاة خطأ عند إرسال الإيميل لتفعيل catch (\Exception) دون التأثير على قاعدة البيانات
        Mail::shouldReceive('to')
            ->andThrow(new \Exception('Mail service failure'));

        $response = $this->postJson('/api/auth/forgot-password/send-otp', [
            'email' => 'user@test.com'
        ]);

        $response->assertStatus(500)
                 ->assertJsonPath('status', 'error')
                 ->assertJsonPath('message', 'حدث خطأ أثناء إرسال الرمز: Mail service failure');
    }

    /**
     * اختبار أخطاء التحقق من المدخلات في إعادة تعيين كلمة المرور.
     */
    public function test_reset_password_validation_errors()
    {
        $response = $this->postJson('/api/auth/forgot-password/reset', [
            'email' => 'not-an-email',
            'otpCode' => '',
            'newPassword' => '123' // قصيرة جداً (أقل من 6 أحرف)
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email', 'otpCode', 'newPassword']);
    }

    /**
     * اختبار معالجة الاستثناءات العامة في إعادة تعيين كلمة المرور.
     */
    public function test_reset_password_throws_exception()
    {
        DB::table('accounts')->insert([
            'name' => 'User Test',
            'email' => 'user@test.com',
            'phone' => '0912345678',
            'password' => bcrypt('old_password'),
            'role' => 'user',
        ]);

        DB::table('otp_codes')->insert([
            'email' => 'user@test.com',
            'otp_code' => bcrypt('123456'),
            'expires_at' => now()->addMinutes(15)
        ]);

        // محاكاة خطأ عند إرسال إيميل التأكيد لتفعيل catch (\Exception) والـ DB::rollBack دون تدمير DB schema
        Mail::shouldReceive('to')
            ->andThrow(new \Exception('Mail service failure on reset confirmation'));

        $response = $this->postJson('/api/auth/forgot-password/reset', [
            'email' => 'user@test.com',
            'otpCode' => '123456',
            'newPassword' => 'new_secret123'
        ]);

        $response->assertStatus(500)
                 ->assertJsonPath('status', 'error')
                 ->assertJsonPath('message', 'حدث خطأ أثناء إعادة تعيين كلمة المرور.');
    }
}
