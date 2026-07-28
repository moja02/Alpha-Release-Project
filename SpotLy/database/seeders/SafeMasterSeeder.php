<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SafeMasterSeeder extends Seeder
{
    /**
     * Master Safe Seeder to populate SpotLy database with realistic fake data.
     * CRITICAL REQUIREMENT: Append-only mode. Does NOT drop or truncate any tables.
     *
     * @return void
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting SafeMasterSeeder (Safe Append Mode)...');

        DB::beginTransaction();

        try {
            // 1. Ensure Active Manager Record Exists or Create Master Manager
            $managerAccountId = DB::table('accounts')->where('role', 'manager')->value('id');
            $managerId = null;

            if ($managerAccountId) {
                $managerId = DB::table('managers')->where('account_id', $managerAccountId)->value('id');
            }

            if (!$managerId) {
                $managerAccountId = DB::table('accounts')->insertGetId([
                    'name' => 'المدير العام للمواقف',
                    'email' => 'manager.spotly@gmail.com',
                    'phone' => '0912345678',
                    'password' => Hash::make('password123'),
                    'role' => 'manager',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $managerId = DB::table('managers')->insertGetId([
                    'account_id' => $managerAccountId,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // 1b. Create Blocked Managers for Super Admin Testing
            $blockedManagerNames = ['خالد فرج البكوش (محظور)', 'إبراهيم علي الجازوي (محظور)'];

            foreach ($blockedManagerNames as $bIdx => $bmName) {
                $bmAccId = DB::table('accounts')->insertGetId([
                    'name' => $bmName,
                    'email' => "blocked.manager" . rand(100, 999) . "@spotly.com",
                    'phone' => "0929999" . rand(100, 999),
                    'password' => Hash::make('password123'),
                    'role' => 'manager',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('managers')->insertGetId([
                    'account_id' => $bmAccId,
                    'status' => 'blocked',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // 2. Create Realistic Parking Slots / Yards
            $parkingSeeds = [
                ['name' => 'ساحة المدار - طريق الشط', 'location_park' => 'طرابلس، طريق الشط مقابل برج طرابلس', 'total_capacity' => 60, 'available_capacity' => 42, 'latitude' => 32.8951, 'longitude' => 13.1843, 'manager_id' => $managerId],
                ['name' => 'موقف ذات العماد ومركز أداء الأعمال', 'location_park' => 'طرابلس، مجمع ذات العماد الإداري', 'total_capacity' => 50, 'available_capacity' => 18, 'latitude' => 32.8985, 'longitude' => 13.1812, 'manager_id' => $managerId],
                ['name' => 'ساحة الفندق الكبير وميدان الشهداء', 'location_park' => 'طرابلس، شارع الفتح - وسط المدينة', 'total_capacity' => 45, 'available_capacity' => 30, 'latitude' => 32.8920, 'longitude' => 13.1870, 'manager_id' => $managerId],
                ['name' => 'موقف شارع عمر المختار المركزي', 'location_park' => 'طرابلس، شارع عمر المختار', 'total_capacity' => 40, 'available_capacity' => 12, 'latitude' => 32.8850, 'longitude' => 13.1790, 'manager_id' => $managerId],
                ['name' => 'ساحة كشلاف - حي الأندلس', 'location_park' => 'طرابلس، حي الأندلس بالقرب من المعرض', 'total_capacity' => 35, 'available_capacity' => 25, 'latitude' => 32.8790, 'longitude' => 13.1560, 'manager_id' => $managerId],
                ['name' => 'موقف سوق الثلاثاء ومصرف ليبيا الخارجي', 'location_park' => 'طرابلس، دائرة سوق الثلاثاء', 'total_capacity' => 55, 'available_capacity' => 38, 'latitude' => 32.8912, 'longitude' => 13.1720, 'manager_id' => $managerId]
            ];

            $createdParkingIds = [];
            foreach ($parkingSeeds as $pData) {
                $pId = DB::table('parkings')->insertGetId([
                    'name' => $pData['name'],
                    'location_park' => $pData['location_park'],
                    'total_capacity' => $pData['total_capacity'],
                    'available_capacity' => $pData['available_capacity'],
                    'latitude' => $pData['latitude'],
                    'longitude' => $pData['longitude'],
                    'manager_id' => $pData['manager_id'],
                    'employee_id' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $createdParkingIds[] = $pId;
            }

            $this->command->info("✅ Created " . count($createdParkingIds) . " Parking Lots/Yards.");

            // 3. Create 15 Field Employees Assigned to Morning & Evening Shifts
            $employeeNames = [
                'أحمد علي المنصوري', 'محمد سالم الترهوني', 'عمر عبد اللطيف الورفلي',
                'طارق المبروك الغرياني', 'خالد مصطفى المقريف', 'عبد السلام الفيتوري',
                'أسامة بشير الزاوي', 'وليد خليفة الكاديكي', 'حمزة سعد المصراتي',
                'يوسف حسن القرقني', 'أيمن فرج السويحلي', 'مصطفى عمران البكوش',
                'مروان نوري القمودي', 'رمزي مفتاح الشريف', 'زياد الطاهر التاجوري'
            ];

            $shiftRoles = ['الوردية الصباحية', 'الوردية المسائية'];
            $createdEmployeeIds = [];

            foreach ($employeeNames as $idx => $empName) {
                $empEmail = 'emp.' . Str::slug($empName, '') . rand(100, 999) . '@spotly.ly';
                $empPhone = '091' . rand(1000000, 9999999);

                $accId = DB::table('accounts')->insertGetId([
                    'name' => $empName,
                    'email' => $empEmail,
                    'phone' => $empPhone,
                    'password' => Hash::make('password123'),
                    'role' => 'employee',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $assignedParkingId = $createdParkingIds[$idx % count($createdParkingIds)];
                $assignedShiftRole = $shiftRoles[$idx % count($shiftRoles)];

                $empId = DB::table('employees')->insertGetId([
                    'account_id' => $accId,
                    'parking_id' => $assignedParkingId,
                    'shift_role' => $assignedShiftRole,
                    'bank_account_number' => 'LY03' . rand(10000000, 99999999) . rand(1000, 9999),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('parkings')->where('id', $assignedParkingId)->whereNull('employee_id')->update(['employee_id' => $empId]);
                $createdEmployeeIds[] = $empId;
            }

            $this->command->info("✅ Created 15 Field Employees assigned to Morning & Evening Shifts.");

            // 4. Create Active Drivers & Blocked Drivers / Violators
            $driverNames = [
                'سفيان خالد عبد الله', 'نادر خليفة الدباشي', 'هيثم علي القمودي',
                'عصام إبراهيم الفرجاني', 'حاتم عمران الشريف', 'وسيم جلال البشتي',
                'مهند عادل النويصري', 'أنور فرج الساعدي', 'سامي رجب الزنتاني', 'بلال مفتاح البوعيشي'
            ];

            $driverUserAccountIds = [];

            foreach ($driverNames as $dName) {
                $dEmail = 'driver.' . Str::slug($dName, '') . rand(10, 99) . '@gmail.com';
                $dPhone = '092' . rand(1000000, 9999999);
                $plateNum = rand(5, 22) . '-' . rand(10000, 99999);

                $dAccId = DB::table('accounts')->insertGetId([
                    'name' => $dName,
                    'email' => $dEmail,
                    'phone' => $dPhone,
                    'password' => Hash::make('password123'),
                    'role' => 'user',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('users')->insert([
                    'account_id' => $dAccId,
                    'plate_number' => $plateNum,
                    'status' => 'active',
                    'fake_booking_count' => rand(0, 2),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('wallets')->insert([
                    'user_id' => $dAccId,
                    'balance' => rand(50, 300),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $driverUserAccountIds[] = [
                    'account_id' => $dAccId,
                    'plate_number' => $plateNum,
                    'email' => $dEmail
                ];
            }

            // Blocked Drivers (With 3+ violations for Manager Dashboard testing!)
            $blockedDriverNames = [
                'سائق مخالف (محظور 1)', 'سائق مخالف (محظور 2)', 'سائق مخالف (محظور 3)'
            ];

            foreach ($blockedDriverNames as $bIdx => $bdName) {
                $bdEmail = "blocked.user" . rand(100, 999) . "@spotly.com";
                $bdPhone = "0939999" . rand(100, 999);
                $bdPlate = "8-" . (90000 + $bIdx);

                $bdAccId = DB::table('accounts')->insertGetId([
                    'name' => $bdName,
                    'email' => $bdEmail,
                    'phone' => $bdPhone,
                    'password' => Hash::make('password123'),
                    'role' => 'user',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('users')->insert([
                    'account_id' => $bdAccId,
                    'plate_number' => $bdPlate,
                    'status' => 'blocked',
                    'fake_booking_count' => 3 + $bIdx,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('wallets')->insert([
                    'user_id' => $bdAccId,
                    'balance' => '0.00',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $this->command->info("✅ Created Active & Blocked Driver Profiles with Plate Numbers & Wallets.");

            // 5. Create 50 Realistic Bookings
            $bookingStatuses = ['confirmed', 'cancelled'];
            $bookingTypes = ['initial', 'actual'];

            for ($i = 1; $i <= 50; $i++) {
                $driverItem = $driverUserAccountIds[array_rand($driverUserAccountIds)];
                $randomParkingId = $createdParkingIds[array_rand($createdParkingIds)];
                $status = $bookingStatuses[rand(0, count($bookingStatuses) - 1)];
                $bType = $bookingTypes[rand(0, 1)];

                $startTime = now()->subHours(rand(1, 48));
                $endTime = (clone $startTime)->addMinutes(rand(30, 180));
                $cost = ($bType === 'actual') ? (rand(1, 4) * 2.5) : 0.00;

                DB::table('bookings')->insert([
                    'user_id' => $driverItem['account_id'],
                    'parking_id' => $randomParkingId,
                    'plate_number' => $driverItem['plate_number'],
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'type' => $bType,
                    'status' => $status,
                    'cost' => $cost,
                    'created_at' => $startTime,
                    'updated_at' => now(),
                ]);
            }

            // 6. Create 30 Shift Attendance Logs
            $shiftNotes = [
                'مناوبة اعتيادية - حركة سير ممتازة ومتابعة حثيثة',
                'تم التفتيش الميداني وصيانة كشك التحصيل الإلكتروني',
                'مناوبة مسائية هادئة وتسليم صندوق الكاش للمشرف',
                'حضور وانصراف حسب الجدول الزمني ومطابقة الشواغر'
            ];

            foreach ($createdEmployeeIds as $empIdItem) {
                DB::table('employee_shifts')->insert([
                    'employee_id' => $empIdItem,
                    'clock_in_at' => now()->subHours(rand(1, 4)),
                    'clock_out_at' => null,
                    'status' => 'active',
                    'notes' => 'مناوبة ميدانية نشطة حالياً',
                    'created_at' => now()->subHours(rand(1, 4)),
                    'updated_at' => now(),
                ]);

                $pastClockIn = now()->subDays(rand(1, 3))->subHours(rand(5, 10));
                $pastClockOut = (clone $pastClockIn)->addHours(rand(6, 8));

                DB::table('employee_shifts')->insert([
                    'employee_id' => $empIdItem,
                    'clock_in_at' => $pastClockIn,
                    'clock_out_at' => $pastClockOut,
                    'status' => 'completed',
                    'notes' => $shiftNotes[array_rand($shiftNotes)],
                    'created_at' => $pastClockIn,
                    'updated_at' => $pastClockOut,
                ]);
            }

            // 7. Activity Cash Audit Logs
            for ($c = 1; $c <= 20; $c++) {
                $empId = $createdEmployeeIds[array_rand($createdEmployeeIds)];
                $pId = $createdParkingIds[array_rand($createdParkingIds)];
                $drv = $driverUserAccountIds[array_rand($driverUserAccountIds)];

                DB::table('activity_cash_audit_logs')->insert([
                    'employee_id' => $empId,
                    'parking_id' => $pId,
                    'operation_type' => ['entry', 'exit', 'recharge'][rand(0, 2)],
                    'plate_number' => $drv['plate_number'],
                    'cash_value' => rand(1, 3) * 2.50,
                    'driver_account_id' => $drv['account_id'],
                    'created_at' => now()->subHours(rand(1, 48)),
                    'updated_at' => now(),
                ]);
            }

            // 8. Recharge Requests
            for ($r = 1; $r <= 10; $r++) {
                $drv = $driverUserAccountIds[array_rand($driverUserAccountIds)];
                $pId = $createdParkingIds[array_rand($createdParkingIds)];

                DB::table('recharge_requests')->insert([
                    'user_id' => $drv['account_id'],
                    'parking_id' => $pId,
                    'requested_points' => rand(20, 100),
                    'receipt_file' => 'receipts/demo_receipt_' . rand(1, 3) . '.png',
                    'status' => ['Pending', 'Approved', 'Rejected'][rand(0, 2)],
                    'created_at' => now()->subHours(rand(1, 24)),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            $this->command->info('🎉 SafeMasterSeeder executed successfully! SpotLy database is fully populated and production-ready.');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("❌ Seeder failed: " . $e->getMessage());
            throw $e;
        }
    }
}
