<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\SpotlyNotificationMail;

class AccountControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    protected function tearDown(): void
    {
        while (DB::transactionLevel() > 0) {
            DB::rollBack();
        }
        parent::tearDown();
    }

    /*
    |--------------------------------------------------------------------------
    | createAccount Tests
    |--------------------------------------------------------------------------
    */

    public function test_create_account_user_role_success()
    {
        $response = $this->postJson('/api/accounts/create', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '123456789',
            'role' => 'user',
            'plateNumber' => 'AB-12345'
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('status', 'success');
        $response->assertJsonStructure(['status', 'accountId']);

        $accountId = $response->json('accountId');

        // Assert database record exists in accounts
        $this->assertDatabaseHas('accounts', [
            'id' => $accountId,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '123456789',
            'role' => 'user',
        ]);

        // Assert database record exists in users
        $this->assertDatabaseHas('users', [
            'account_id' => $accountId,
            'plate_number' => 'AB-12345',
            'status' => 'active',
            'fake_booking_count' => 0,
        ]);

        // Assert database record exists in wallets
        $this->assertDatabaseHas('wallets', [
            'user_id' => $accountId,
            'balance' => 0.00,
        ]);

        // Assert database record exists in notifications
        $this->assertDatabaseHas('notifications', [
            'user_id' => $accountId,
            'type' => 'Account_Created',
            'sent_to_email' => 'john@example.com',
        ]);

        // Assert mail was sent
        Mail::assertSent(SpotlyNotificationMail::class, function ($mail) {
            return $mail->hasTo('john@example.com') && 
                   str_contains($mail->mailDetails['body'], 'John Doe');
        });
    }

    public function test_create_account_employee_role_success()
    {
        $response = $this->postJson('/api/accounts/create', [
            'name' => 'Employee Alice',
            'email' => 'alice@example.com',
            'phone' => '987654321',
            'role' => 'employee'
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('status', 'success');

        $accountId = $response->json('accountId');

        // Assert accounts table has the record
        $this->assertDatabaseHas('accounts', [
            'id' => $accountId,
            'role' => 'employee',
        ]);

        // Since it is an employee, it should NOT insert into users or wallets tables
        $this->assertDatabaseMissing('users', [
            'account_id' => $accountId,
        ]);

        $this->assertDatabaseMissing('wallets', [
            'user_id' => $accountId,
        ]);

        // Notifications are missing since employee does not have a users table record
        $this->assertDatabaseMissing('notifications', [
            'user_id' => $accountId,
        ]);

        Mail::assertSent(SpotlyNotificationMail::class, function ($mail) {
            return $mail->hasTo('alice@example.com');
        });
    }

    public function test_create_account_validation_missing_fields()
    {
        $response = $this->postJson('/api/accounts/create', []);

        $response->assertStatus(500); // Because validation failure throws ValidationException caught by catch block and returned as 500
        $response->assertJsonPath('status', 'error');
    }

    public function test_create_account_validation_user_missing_platenumber()
    {
        $response = $this->postJson('/api/accounts/create', [
            'name' => 'John Plate',
            'email' => 'johnplate@example.com',
            'phone' => '123456789',
            'role' => 'user'
            // plateNumber is missing
        ]);

        $response->assertStatus(500);
        $response->assertJsonPath('status', 'error');
    }

    public function test_create_account_validation_duplicate_email()
    {
        // First create an account
        DB::table('accounts')->insert([
            'name' => 'Existing User',
            'email' => 'duplicate@example.com',
            'phone' => '111111111',
            'password' => 'hashed_password',
            'role' => 'user',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $response = $this->postJson('/api/accounts/create', [
            'name' => 'New User',
            'email' => 'duplicate@example.com',
            'phone' => '222222222',
            'role' => 'user',
            'plateNumber' => 'XY-99999'
        ]);

        $response->assertStatus(500);
        $response->assertJsonPath('status', 'error');
    }

    public function test_create_account_throws_exception_rolls_back()
    {
        $db = DB::getFacadeRoot();
        $originalConnection = DB::connection();

        $mockConnection = \Mockery::mock($originalConnection)->makePartial();
        $mockConnection->shouldReceive('table')
            ->with('notifications')
            ->andThrow(new \Exception('Database failure on notifications insertion'));

        $ref = new \ReflectionProperty(get_class($db), 'connections');
        $ref->setAccessible(true);
        $connections = $ref->getValue($db);
        $connections['sqlite'] = $mockConnection;
        $ref->setValue($db, $connections);

        try {
            $response = $this->postJson('/api/accounts/create', [
                'name' => 'Error User',
                'email' => 'error@example.com',
                'phone' => '555555555',
                'role' => 'user',
                'plateNumber' => 'ERR-111'
            ]);
        } finally {
            $connections['sqlite'] = $originalConnection;
            $ref->setValue($db, $connections);
        }

        $response->assertStatus(500);
        $response->assertJsonPath('status', 'error');
        $response->assertJsonPath('message', 'Database failure on notifications insertion');

        // Verify transaction rolled back (no account was created in DB)
        $this->assertDatabaseMissing('accounts', [
            'email' => 'error@example.com'
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | getUserNotifications Tests
    |--------------------------------------------------------------------------
    */

    public function test_get_user_notifications_success()
    {
        $userId = DB::table('accounts')->insertGetId([
            'name' => 'Notify User',
            'email' => 'notify@example.com',
            'phone' => '111111111',
            'password' => 'hashed',
            'role' => 'user',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('users')->insert([
            'id' => $userId,
            'account_id' => $userId,
            'plate_number' => 'OB-NOTIFY',
            'status' => 'active',
            'fake_booking_count' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('notifications')->insert([
            'user_id' => $userId,
            'message' => 'Notification 1',
            'type' => 'Info',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('notifications')->insert([
            'user_id' => $userId,
            'message' => 'Notification 2',
            'type' => 'Alert',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Different user's notification
        $otherUserId = DB::table('accounts')->insertGetId([
            'name' => 'Other Notify User',
            'email' => 'othernotify@example.com',
            'phone' => '222222222',
            'password' => 'hashed',
            'role' => 'user',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('users')->insert([
            'id' => $otherUserId,
            'account_id' => $otherUserId,
            'plate_number' => 'OB-OTHER',
            'status' => 'active',
            'fake_booking_count' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('notifications')->insert([
            'user_id' => $otherUserId,
            'message' => 'Notification Other',
            'type' => 'Info',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $response = $this->getJson("/api/notifications?userId={$userId}");

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');
        $response->assertJsonCount(2, 'data');
        $response->assertJsonPath('data.0.message', 'Notification 2'); // Ordered by id desc
        $response->assertJsonPath('data.1.message', 'Notification 1');
    }

    public function test_get_user_notifications_throws_exception()
    {
        $db = DB::getFacadeRoot();
        $originalConnection = DB::connection();

        $mockConnection = \Mockery::mock($originalConnection)->makePartial();
        $mockConnection->shouldReceive('table')
            ->with('notifications')
            ->andThrow(new \Exception('Database error fetching notifications'));

        $ref = new \ReflectionProperty(get_class($db), 'connections');
        $ref->setAccessible(true);
        $connections = $ref->getValue($db);
        $connections['sqlite'] = $mockConnection;
        $ref->setValue($db, $connections);

        try {
            $response = $this->getJson('/api/notifications?userId=999');
        } finally {
            $connections['sqlite'] = $originalConnection;
            $ref->setValue($db, $connections);
        }

        $response->assertStatus(500);
        $response->assertJsonPath('status', 'error');
        $response->assertJsonPath('message', 'Database error fetching notifications');
    }
}
