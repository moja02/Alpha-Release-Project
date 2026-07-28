<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Account;
use App\Models\Manager;
use App\Models\Parking;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use Carbon\Carbon;

class SuperAdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    private $adminAccount;
    private $nonAdminAccount;
    private $managerAccount;

    protected function setUp(): void
    {
        parent::setUp();

        // Create Admin (Super Admin)
        $this->adminAccount = Account::create([
            'name' => 'Super Admin',
            'email' => 'admin@spotly.com',
            'phone' => '0911111111',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        // Create Non-Admin Account (e.g. driver/user)
        $this->nonAdminAccount = Account::create([
            'name' => 'Regular User',
            'email' => 'user@spotly.com',
            'phone' => '0922222222',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        // Create Manager account & record
        $this->managerAccount = Account::create([
            'name' => 'Manager User',
            'email' => 'manager@spotly.com',
            'phone' => '0933333333',
            'password' => Hash::make('password123'),
            'role' => 'manager',
        ]);
        
        DB::table('managers')->insert([
            'account_id' => $this->managerAccount->id,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_guests_cannot_access_super_admin_endpoints()
    {
        $response = $this->getJson('/api/v1/super-admin/dashboard/statistics');
        $response->assertStatus(401);
    }

    public function test_non_admins_cannot_access_super_admin_endpoints()
    {
        Sanctum::actingAs($this->nonAdminAccount);
        $response = $this->getJson('/api/v1/super-admin/dashboard/statistics');
        $response->assertStatus(403);
        
        Sanctum::actingAs($this->managerAccount);
        $response = $this->getJson('/api/v1/super-admin/dashboard/statistics');
        $response->assertStatus(403);
    }

    public function test_admin_can_fetch_dashboard_statistics_with_caching()
    {
        Cache::flush();

        // Create a parking lot
        $parking = Parking::create([
            'name' => 'Central Parking',
            'location_park' => 'Downtown',
            'total_capacity' => 100,
            'available_capacity' => 80,
        ]);

        Sanctum::actingAs($this->adminAccount);
        $response = $this->getJson('/api/v1/super-admin/dashboard/statistics');
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'data' => [
                'summary' => [
                    'total_parking_lots' => 1,
                    'total_active_managers' => 1,
                ],
                'occupancy' => [
                    'global_capacity' => 100,
                    'occupied_spots' => 20,
                    'occupancy_rate_percentage' => 20.0,
                ]
            ]
        ]);

        // Verify it was cached
        $this->assertTrue(Cache::has('super_admin_dashboard_stats'));
    }

    public function test_admin_can_fetch_financial_reports()
    {
        // 1. Create parking
        $parking = Parking::create([
            'name' => 'Central Parking',
            'location_park' => 'Downtown',
            'total_capacity' => 100,
            'available_capacity' => 100,
        ]);

        // 2. Create User account & user record
        $userAcc = Account::create([
            'name' => 'Driver',
            'email' => 'driver@test.com',
            'phone' => '0999999999',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);
        
        $userId = DB::table('users')->insertGetId([
            'account_id' => $userAcc->id,
            'plate_number' => 'ABC-123',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Create a booking using Carbon objects
        DB::table('bookings')->insert([
            'user_id' => $userId,
            'parking_id' => $parking->id,
            'plate_number' => 'ABC-123',
            'start_time' => Carbon::now()->subDays(2),
            'end_time' => Carbon::now()->subDays(2)->addHours(2),
            'type' => 'actual',
            'status' => 'confirmed',
            'cost' => 50.00,
            'refund_amount' => 10.00,
            'created_at' => Carbon::now()->subDays(2),
            'updated_at' => Carbon::now()->subDays(2),
        ]);

        Sanctum::actingAs($this->adminAccount);
        $response = $this->getJson('/api/v1/super-admin/dashboard/financial-reports?start_date=' . Carbon::now()->subDays(5)->toDateString() . '&end_date=' . Carbon::now()->toDateString());
        
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'data' => [
                'metrics' => [
                    'total_earnings' => 50.00,
                    'refunded_earnings' => 10.00,
                    'net_earnings' => 40.00
                ],
                'revenue_by_parking' => [
                    [
                        'parking_name' => 'Central Parking',
                        'gross_revenue' => 50.00,
                        'refunds' => 10.00,
                        'net_revenue' => 40.00
                    ]
                ]
            ]
        ]);
    }

    public function test_parking_crud_and_validation()
    {
        Sanctum::actingAs($this->adminAccount);

        // CREATE
        $response = $this->postJson('/api/v1/super-admin/parkings', [
            'name' => 'Central Park',
            'location_park' => 'Downtown',
            'total_capacity' => 50,
        ]);
        $response->assertStatus(201);
        $parkingId = $response->json('data.id');

        $this->assertDatabaseHas('parkings', ['id' => $parkingId, 'name' => 'Central Park']);

        // UPDATE (Fail when capacity is below occupied spots)
        // Let's manually set available capacity to 10 (occupied = 40)
        Parking::where('id', $parkingId)->update(['available_capacity' => 10]);

        $response = $this->putJson("/api/v1/super-admin/parkings/{$parkingId}", [
            'total_capacity' => 30, // less than occupied (40)
        ]);
        $response->assertStatus(422);

        // UPDATE (Success)
        $response = $this->putJson("/api/v1/super-admin/parkings/{$parkingId}", [
            'total_capacity' => 60,
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('parkings', ['id' => $parkingId, 'total_capacity' => 60, 'available_capacity' => 20]); // 60 - 40 = 20

        // DELETE (Success)
        $response = $this->deleteJson("/api/v1/super-admin/parkings/{$parkingId}");
        $response->assertStatus(200);
        $this->assertDatabaseMissing('parkings', ['id' => $parkingId]);
    }

    public function test_manager_crud_and_assignments()
    {
        Sanctum::actingAs($this->adminAccount);

        // CREATE MANAGER
        $response = $this->postJson('/api/v1/super-admin/managers', [
            'name' => 'New Manager',
            'email' => 'newmanager@spotly.com',
            'phone' => '0777777777',
            'password' => 'password123',
        ]);
        $response->assertStatus(201);
        $managerId = $response->json('data.manager_id');

        $this->assertDatabaseHas('accounts', ['email' => 'newmanager@spotly.com', 'role' => 'manager']);
        $this->assertDatabaseHas('managers', ['id' => $managerId]);

        // CREATE PARKINGS
        $parking1 = Parking::create([
            'name' => 'P1',
            'location_park' => 'L1',
            'total_capacity' => 10,
            'available_capacity' => 10,
        ]);
        $parking2 = Parking::create([
            'name' => 'P2',
            'location_park' => 'L2',
            'total_capacity' => 20,
            'available_capacity' => 20,
        ]);

        // ASSIGN
        $response = $this->postJson("/api/v1/super-admin/managers/{$managerId}/assign-parking", [
            'parking_ids' => [$parking1->id],
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('parkings', ['id' => $parking1->id, 'manager_id' => $managerId]);

        // REASSIGN
        $response = $this->postJson("/api/v1/super-admin/managers/{$managerId}/reassign-parking", [
            'parking_ids' => [$parking2->id],
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('parkings', ['id' => $parking2->id, 'manager_id' => $managerId]);
        $this->assertDatabaseHas('parkings', ['id' => $parking1->id, 'manager_id' => null]);

        // UPDATE STATUS
        $response = $this->putJson("/api/v1/super-admin/managers/{$managerId}/status", [
            'status' => 'blocked',
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('managers', ['id' => $managerId, 'status' => 'blocked']);

        // DELETE
        $response = $this->deleteJson("/api/v1/super-admin/managers/{$managerId}");
        $response->assertStatus(200);
        $this->assertDatabaseMissing('managers', ['id' => $managerId]);
    }
}
