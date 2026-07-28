<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ManagerUnblockUsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_view_violating_users_and_unblock_them()
    {
        // 1. Create a manager account and manager record
        $managerAccount = Account::create([
            'name' => 'Test Manager',
            'email' => 'manager@test.com',
            'phone' => '123456789',
            'password' => Hash::make('password123'),
            'role' => 'manager',
        ]);

        $managerId = DB::table('managers')->insertGetId([
            'account_id' => $managerAccount->id,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Create user (driver) account
        $driverAccount = Account::create([
            'name' => 'Violating Driver',
            'email' => 'driver@test.com',
            'phone' => '999888777',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        // 3. Create user profile in users table (blocked and with 3 fake bookings)
        $userId = DB::table('users')->insertGetId([
            'account_id' => $driverAccount->id,
            'plate_number' => 'OB-12345',
            'status' => 'blocked',
            'fake_booking_count' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create another driver who has a violation but status is active (not blocked)
        $activeDriverAccount = Account::create([
            'name' => 'Active Driver With Violation',
            'email' => 'active_violator@test.com',
            'phone' => '555444333',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        $activeDriverId = DB::table('users')->insertGetId([
            'account_id' => $activeDriverAccount->id,
            'plate_number' => 'OB-54321',
            'status' => 'active',
            'fake_booking_count' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4. Try to access manager dashboard as guest -> redirects to login
        $response = $this->get('/manager/dashboard');
        $response->assertRedirect('/login');

        // 5. Access as manager -> success (200) and sees the blocked user
        $response = $this->actingAs($managerAccount)->get('/manager/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Violating Driver');
        $response->assertSee('OB-12345');
        $response->assertSee('confirmUnblockDriver(' . $userId);

        $response->assertDontSee('Active Driver With Violation');
        $response->assertDontSee('OB-54321');

        // 6. Unblock the driver
        $unblockResponse = $this->actingAs($managerAccount)->postJson("/manager/users/unblock", [
            'user_id' => $userId
        ]);

        $unblockResponse->assertStatus(200);
        $unblockResponse->assertJson(['status' => 'success']);

        // 7. Verify the database state is updated (status => active, fake_booking_count => 0)
        $this->assertDatabaseHas('users', [
            'id' => $userId,
            'status' => 'active',
            'fake_booking_count' => 0
        ]);
    }

    public function test_non_manager_cannot_unblock_users()
    {
        // 1. Create a non-manager account (e.g., driver user)
        $userAccount = Account::create([
            'name' => 'Ordinary User',
            'email' => 'user@test.com',
            'phone' => '111222333',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        // 2. Create another user (driver) who is blocked
        $driverAccount = Account::create([
            'name' => 'Violating Driver',
            'email' => 'driver@test.com',
            'phone' => '999888777',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        $userId = DB::table('users')->insertGetId([
            'account_id' => $driverAccount->id,
            'plate_number' => 'OB-12345',
            'status' => 'blocked',
            'fake_booking_count' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Try to unblock as guest -> redirects to login / unauthorized
        $response = $this->postJson("/manager/users/unblock", ['user_id' => $userId]);
        $response->assertStatus(401); // Unauthorized by auth middleware

        // 4. Try to unblock as user -> redirects to /login by role:manager middleware
        $response = $this->actingAs($userAccount)->postJson("/manager/users/unblock", ['user_id' => $userId]);
        $response->assertStatus(403);
    }
}
