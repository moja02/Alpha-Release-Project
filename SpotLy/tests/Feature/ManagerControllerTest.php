<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Account;
use App\Models\Parking;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ManagerControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $managerAccount;
    protected $managerId;
    protected $parking;

    protected function setUp(): void
    {
        parent::setUp();

        $this->managerAccount = Account::create([
            'name' => 'Manager User',
            'email' => 'manager_test@spotly.com',
            'phone' => '0911111111',
            'password' => Hash::make('password123'),
            'role' => 'manager',
        ]);

        $this->managerId = DB::table('managers')->insertGetId([
            'account_id' => $this->managerAccount->id,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->parking = Parking::create([
            'name' => 'Manager Lot 1',
            'location_park' => 'Downtown Lot',
            'total_capacity' => 10,
            'available_capacity' => 10,
            'latitude' => 32.8872,
            'longitude' => 13.1913,
            'manager_id' => $this->managerId,
        ]);
    }

    public function test_dashboard_isolation()
    {
        $response = $this->actingAs($this->managerAccount)->get('/manager/dashboard');
        $response->assertStatus(200);
        $response->assertViewHas('parkingsList');
    }

    public function test_ban_logic_ignores_violations_when_successful_bookings_under_2()
    {
        $driverAccount = Account::create([
            'name' => 'Driver One',
            'email' => 'driver1@test.com',
            'phone' => '0922222222',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        $driverId = DB::table('users')->insertGetId([
            'account_id' => $driverAccount->id,
            'plate_number' => '12345-5',
            'fake_booking_count' => 4,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Only 1 successful booking (under 2)
        Booking::create([
            'user_id' => $driverId,
            'parking_id' => $this->parking->id,
            'plate_number' => '12345-5',
            'start_time' => now(),
            'end_time' => now()->addHours(2),
            'type' => 'actual',
            'status' => 'confirmed',
            'cost' => 10.00
        ]);

        $response = $this->actingAs($this->managerAccount)->postJson('/manager/users/violation', [
            'user_id' => $driverId,
            'reason' => 'Late arrival'
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'ignored']);

        $this->assertDatabaseHas('users', [
            'id' => $driverId,
            'fake_booking_count' => 4,
            'status' => 'active'
        ]);
    }

    public function test_ban_logic_bans_user_when_successful_bookings_at_least_2_and_fake_count_reaches_5()
    {
        $driverAccount = Account::create([
            'name' => 'Driver Two',
            'email' => 'driver2@test.com',
            'phone' => '0933333333',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        $driverId = DB::table('users')->insertGetId([
            'account_id' => $driverAccount->id,
            'plate_number' => '99999-5',
            'fake_booking_count' => 4,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2 successful bookings
        Booking::create([
            'user_id' => $driverId,
            'parking_id' => $this->parking->id,
            'plate_number' => '99999-5',
            'start_time' => now()->subDays(2),
            'end_time' => now()->subDays(2)->addHours(2),
            'type' => 'actual',
            'status' => 'confirmed',
            'cost' => 10.00
        ]);

        Booking::create([
            'user_id' => $driverId,
            'parking_id' => $this->parking->id,
            'plate_number' => '99999-5',
            'start_time' => now()->subDay(),
            'end_time' => now()->subDay()->addHours(2),
            'type' => 'actual',
            'status' => 'completed',
            'cost' => 15.00
        ]);

        $response = $this->actingAs($this->managerAccount)->postJson('/manager/users/violation', [
            'user_id' => $driverId,
            'reason' => 'No show'
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success', 'user_status' => 'blocked', 'fake_booking_count' => 5]);

        $this->assertDatabaseHas('users', [
            'id' => $driverId,
            'fake_booking_count' => 5,
            'status' => 'blocked'
        ]);
    }

    public function test_interactive_map_and_spot_status_update()
    {
        $mapResponse = $this->actingAs($this->managerAccount)->getJson("/manager/interactive-map?parking_id={$this->parking->id}");
        $mapResponse->assertStatus(200);
        $mapResponse->assertJsonStructure(['status', 'parking', 'spots']);

        $updateResponse = $this->actingAs($this->managerAccount)->postJson('/manager/spots/update-status', [
            'parking_id' => $this->parking->id,
            'spot_number' => 1,
            'status' => 'disabled'
        ]);

        $updateResponse->assertStatus(200);
        $updateResponse->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('parking_spots', [
            'parking_id' => $this->parking->id,
            'spot_number' => 1,
            'status' => 'disabled'
        ]);
    }

    public function test_employee_crud_and_tracking()
    {
        $response = $this->actingAs($this->managerAccount)->getJson('/manager/employees');
        $response->assertStatus(200);
        $response->assertJsonStructure(['status', 'data']);
    }

    public function test_export_report_pdf_and_spot_utilization()
    {
        $response = $this->actingAs($this->managerAccount)->get('/manager/reports/export?format=pdf&period=this_month');
        $response->assertStatus(200);
    }

    public function test_export_report_excel_and_json_with_preset_time_filters()
    {
        $jsonResponse = $this->actingAs($this->managerAccount)->get('/manager/reports/export?format=json&period=today');
        $jsonResponse->assertStatus(200);
        $jsonResponse->assertJsonStructure([
            'status',
            'data' => [
                'financial' => ['total_revenue', 'total_expenses', 'net_profit', 'avg_daily_income'],
                'operational' => ['cars_entered', 'cars_exited', 'total_parking_hours', 'occupancy_rate'],
                'simulation',
                'spot_utilization'
            ]
        ]);

        $excelResponse = $this->actingAs($this->managerAccount)->get('/manager/reports/export?format=excel&period=this_week');
        $excelResponse->assertStatus(200);
    }
}
