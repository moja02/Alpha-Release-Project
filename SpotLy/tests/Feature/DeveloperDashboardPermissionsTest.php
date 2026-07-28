<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class DeveloperDashboardPermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_developer_can_access_developer_dashboard()
    {
        $developer = Account::create([
            'name' => 'Developer User',
            'email' => 'developer@test.com',
            'phone' => '1234567890',
            'password' => Hash::make('password123'),
            'role' => 'developer',
        ]);

        $response = $this->actingAs($developer)->get('/developer/dashboard');
        $response->assertStatus(200);
    }

    public function test_manager_can_access_developer_dashboard()
    {
        $manager = Account::create([
            'name' => 'Manager User',
            'email' => 'manager@test.com',
            'phone' => '1234567890',
            'password' => Hash::make('password123'),
            'role' => 'manager',
        ]);

        \Illuminate\Support\Facades\DB::table('managers')->insert([
            'account_id' => $manager->id,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($manager)->get('/developer/dashboard');
        $response->assertStatus(200);
    }

    public function test_employee_cannot_access_developer_dashboard()
    {
        $employee = Account::create([
            'name' => 'Employee User',
            'email' => 'employee@test.com',
            'phone' => '1234567890',
            'password' => Hash::make('password123'),
            'role' => 'employee',
        ]);

        $response = $this->actingAs($employee)->get('/developer/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_driver_cannot_access_developer_dashboard()
    {
        $driver = Account::create([
            'name' => 'Driver User',
            'email' => 'driver@test.com',
            'phone' => '1234567890',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        $response = $this->actingAs($driver)->get('/developer/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_manager_can_store_parking()
    {
        $manager = Account::create([
            'name' => 'Manager User',
            'email' => 'manager@test.com',
            'phone' => '1234567890',
            'password' => Hash::make('password123'),
            'role' => 'manager',
        ]);

        $managerId = \Illuminate\Support\Facades\DB::table('managers')->insertGetId([
            'account_id' => $manager->id,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($manager)->postJson('/manager/parkings/store', [
            'name' => 'Test Parking Area',
            'location_park' => 'Near Central Mall',
            'total_capacity' => 100,
            'latitude' => 32.8943,
            'longitude' => 13.1804
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('parkings', [
            'name' => 'Test Parking Area',
            'manager_id' => $managerId
        ]);
    }

    public function test_non_manager_cannot_store_parking()
    {
        $developer = Account::create([
            'name' => 'Developer User',
            'email' => 'developer@test.com',
            'phone' => '1234567890',
            'password' => Hash::make('password123'),
            'role' => 'developer',
        ]);

        $response = $this->actingAs($developer)->postJson('/manager/parkings/store', [
            'name' => 'Unauthorized Area',
            'location_park' => 'Nowhere St',
            'total_capacity' => 50,
            'latitude' => 32.8943,
            'longitude' => 13.1804
        ]);

        $response->assertStatus(403);
    }

    public function test_developer_can_delete_manager_account()
    {
        $developer = Account::create([
            'name' => 'Developer User',
            'email' => 'developer@test.com',
            'phone' => '1234567890',
            'password' => Hash::make('password123'),
            'role' => 'developer',
        ]);

        $managerAccount = Account::create([
            'name' => 'Manager To Delete',
            'email' => 'todelete@test.com',
            'phone' => '0999999999',
            'password' => Hash::make('password123'),
            'role' => 'manager',
        ]);

        $managerId = \Illuminate\Support\Facades\DB::table('managers')->insertGetId([
            'account_id' => $managerAccount->id,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $parking = \App\Models\Parking::create([
            'name' => 'Assigned Parking',
            'location_park' => 'Location X',
            'total_capacity' => 20,
            'available_capacity' => 20,
            'manager_id' => $managerId
        ]);

        $response = $this->actingAs($developer)->deleteJson("/developer/managers/{$managerId}");

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $this->assertDatabaseMissing('managers', ['id' => $managerId]);
        $this->assertDatabaseMissing('accounts', ['id' => $managerAccount->id]);
        $this->assertDatabaseHas('parkings', ['id' => $parking->id, 'manager_id' => null]);
    }

    public function test_developer_can_delete_parking_lot()
    {
        $developer = Account::create([
            'name' => 'Developer User',
            'email' => 'developer@test.com',
            'phone' => '1234567890',
            'password' => Hash::make('password123'),
            'role' => 'developer',
        ]);

        $parking = \App\Models\Parking::create([
            'name' => 'Parking To Delete',
            'location_park' => 'Location Y',
            'total_capacity' => 15,
            'available_capacity' => 15,
        ]);

        $response = $this->actingAs($developer)->deleteJson("/developer/parkings/{$parking->id}");

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);
        $this->assertDatabaseMissing('parkings', ['id' => $parking->id]);
    }
}
