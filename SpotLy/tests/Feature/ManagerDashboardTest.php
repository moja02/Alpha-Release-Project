<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ManagerDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_dashboard_access_and_employee_management()
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

        // 2. Create a parking record assigned to this manager
        $parkingId = DB::table('parkings')->insertGetId([
            'name' => 'Al-Nasr Parking',
            'location_park' => 'Tripoli, Al-Nasr St',
            'total_capacity' => 50,
            'available_capacity' => 50,
            'manager_id' => $managerId,
            'employee_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Try to access the dashboard as guest -> redirects to login
        $response = $this->get('/manager/dashboard');
        $response->assertRedirect('/login');

        // 4. Access as manager -> success (200) and displays the parking name
        $response = $this->actingAs($managerAccount)->get('/manager/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Al-Nasr Parking');
        
        // Because there is no employee linked, it should show the "Create Employee" button
        $response->assertSee('openCreateEmployeeModal(' . $parkingId);
        $response->assertDontSee('confirmUnlinkEmployee(' . $parkingId);

        // 5. Create an employee assigned to this parking yard
        $storeResponse = $this->actingAs($managerAccount)->postJson('/manager/employees/store', [
            'name' => 'John Doe',
            'email' => 'johndoe@test.com',
            'phone' => '0911223344',
            'bank_account_number' => 'LY123456789',
            'parking_id' => $parkingId,
        ]);

        $storeResponse->assertStatus(200);
        $storeResponse->assertJson(['status' => 'success']);

        // Verify the database has the employee account and assignment
        $this->assertDatabaseHas('accounts', [
            'email' => 'johndoe@test.com',
            'role' => 'employee'
        ]);

        $employeeAccount = Account::where('email', 'johndoe@test.com')->first();
        $employee = DB::table('employees')->where('account_id', $employeeAccount->id)->first();
        $this->assertNotNull($employee);

        // Verify parking is now linked to employee
        $this->assertDatabaseHas('parkings', [
            'id' => $parkingId,
            'employee_id' => $employee->id
        ]);

        // Refresh dashboard and verify the unlinking button is now displayed and create button is hidden
        $response = $this->actingAs($managerAccount)->get('/manager/dashboard');
        $response->assertStatus(200);
        $response->assertSee('confirmUnlinkEmployee(' . $parkingId);
        $response->assertDontSee('openCreateEmployeeModal(' . $parkingId);

        // 6. Unlink the employee
        $unlinkResponse = $this->actingAs($managerAccount)->postJson('/manager/parkings/unlink', [
            'parking_id' => $parkingId,
        ]);

        $unlinkResponse->assertStatus(200);
        $unlinkResponse->assertJson(['status' => 'success']);

        // Verify parking is no longer linked to employee
        $this->assertDatabaseHas('parkings', [
            'id' => $parkingId,
            'employee_id' => null
        ]);

        // Refresh dashboard and verify the create button is visible again
        $response = $this->actingAs($managerAccount)->get('/manager/dashboard');
        $response->assertStatus(200);
        $response->assertSee('openCreateEmployeeModal(' . $parkingId);
        $response->assertDontSee('confirmUnlinkEmployee(' . $parkingId);
    }

    public function test_manager_can_assign_existing_unlinked_employee()
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

        // 2. Create an unassigned employee account and employee record
        $employeeAccount = Account::create([
            'name' => 'Existing Unlinked Employee',
            'email' => 'existing@test.com',
            'phone' => '0999999999',
            'password' => Hash::make('password123'),
            'role' => 'employee',
        ]);

        $employeeId = DB::table('employees')->insertGetId([
            'account_id' => $employeeAccount->id,
            'bank_account_number' => 'LY000000000',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Create a parking record assigned to this manager
        $parkingId = DB::table('parkings')->insertGetId([
            'name' => 'Al-Nasr Parking',
            'location_park' => 'Tripoli, Al-Nasr St',
            'total_capacity' => 50,
            'available_capacity' => 50,
            'manager_id' => $managerId,
            'employee_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4. Assign the existing employee to the parking yard
        $response = $this->actingAs($managerAccount)->postJson('/manager/employees/store', [
            'assignment_type' => 'select',
            'employee_id' => $employeeId,
            'parking_id' => $parkingId,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        // Verify the database has updated the parking link
        $this->assertDatabaseHas('parkings', [
            'id' => $parkingId,
            'employee_id' => $employeeId
        ]);

        // 5. Try to assign the same employee to another parking - should fail
        $secondParkingId = DB::table('parkings')->insertGetId([
            'name' => 'Second Parking',
            'location_park' => 'Tripoli, Ben Ashour St',
            'total_capacity' => 20,
            'available_capacity' => 20,
            'manager_id' => $managerId,
            'employee_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $secondResponse = $this->actingAs($managerAccount)->postJson('/manager/employees/store', [
            'assignment_type' => 'select',
            'employee_id' => $employeeId,
            'parking_id' => $secondParkingId,
        ]);

        $secondResponse->assertStatus(422);
        $secondResponse->assertJson(['status' => 'error', 'message' => 'هذا الموظف معين بالفعل لساحة أخرى!']);
    }
}
