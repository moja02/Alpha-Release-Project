<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with a comprehensive, rich dataset
     * covering all roles (Developers, Active & Blocked Managers, Employees, Active & Blocked Drivers),
     * Parkings, Bookings, Shift Logs, Cash Audit Logs, Recharge Requests, and Notifications.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('🧹 Truncating old tables and building complete high-traffic SpotLy dataset...');

        // Disable foreign key checks for clean truncation
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        DB::table('activity_cash_audit_logs')->truncate();
        DB::table('employee_shifts')->truncate();
        DB::table('bookings')->truncate();
        DB::table('recharge_requests')->truncate();
        DB::table('notifications')->truncate();
        DB::table('wallets')->truncate();
        DB::table('parkings')->truncate();
        DB::table('employees')->truncate();
        DB::table('managers')->truncate();
        DB::table('users')->truncate();
        DB::table('accounts')->truncate();
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 1. Super Admin / Developer Account
        $developerAccountId = DB::table('accounts')->insertGetId([
            'name' => 'محمد (المطور الرئيسي)',
            'email' => 'developer@spotly.com',
            'phone' => '0910001122',
            'password' => Hash::make('password123'),
            'role' => 'developer',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Managers (Active & Blocked Managers)
        $managerIds = [];

        // Primary Active Manager for demo login
        $primaryManagerAccountId = DB::table('accounts')->insertGetId([
            'name' => 'المدير العام للمواقف',
            'email' => 'manager@spotly.com',
            'phone' => '0920000000',
            'password' => Hash::make('password123'),
            'role' => 'manager',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $primaryManagerId = DB::table('managers')->insertGetId([
            'account_id' => $primaryManagerAccountId,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $managerIds[] = $primaryManagerId;

        // Additional Active Managers
        $managerNames = [
            'سليمان الهادي التومي', 'ناصر عبد السلام المصراتي', 'مصطفى الفيتوري الزاوي',
            'مفتاح خليفة الكاديكي', 'وليد فرج الشريف', 'عبد السلام الورفلي', 'رمزي علي المقريف'
        ];

        foreach ($managerNames as $idx => $mName) {
            $mAccId = DB::table('accounts')->insertGetId([
                'name' => $mName,
                'email' => "manager" . ($idx + 2) . "@spotly.com",
                'phone' => "092" . rand(1000000, 9999999),
                'password' => Hash::make('password123'),
                'role' => 'manager',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $mId = DB::table('managers')->insertGetId([
                'account_id' => $mAccId,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $managerIds[] = $mId;
        }

        // Blocked Managers (To test Super-Admin Unblock functionality!)
        $blockedManagerNames = ['خالد فرج البكوش (محظور)', 'إبراهيم علي الجازوي (محظور)'];

        foreach ($blockedManagerNames as $bIdx => $bmName) {
            $bmAccId = DB::table('accounts')->insertGetId([
                'name' => $bmName,
                'email' => "blocked.manager" . ($bIdx + 1) . "@spotly.com",
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

        // 3. Create 12 Tripoli Parking Yards
        $parkingSeeds = [
            ['name' => 'ساحة ميدان الشهداء التفاعلية', 'location_park' => 'بجانب السراي الحمراء - وسط البلد', 'total_capacity' => 60, 'available_capacity' => 45, 'latitude' => 32.895456, 'longitude' => 13.180324],
            ['name' => 'ساحة برج طرابلس وذات العماد', 'location_park' => 'خلف البرج - مجمع ذات العماد الإداري', 'total_capacity' => 70, 'available_capacity' => 32, 'latitude' => 32.897120, 'longitude' => 13.175110],
            ['name' => 'ساحة المدار - طريق الشط', 'location_park' => 'مقابل ميناء طرابلس البحري وطريق الشط', 'total_capacity' => 80, 'available_capacity' => 58, 'latitude' => 32.901500, 'longitude' => 13.189000],
            ['name' => 'ساحة ميدان الجزائر والبريد', 'location_park' => 'بجوار مبنى البلدية والبريد المركزي', 'total_capacity' => 50, 'available_capacity' => 14, 'latitude' => 32.893200, 'longitude' => 13.190100],
            ['name' => 'ساحة كشلاف - حي الأندلس', 'location_park' => 'حي الأندلس - بجانب نادي الفروسية', 'total_capacity' => 40, 'available_capacity' => 22, 'latitude' => 32.889000, 'longitude' => 13.129000],
            ['name' => 'ساحة قرقارش وسوق الثلاثاء', 'location_park' => 'شارع قرقارش الرئيسي - خلف مصرف الأمان', 'total_capacity' => 65, 'available_capacity' => 39, 'latitude' => 32.876200, 'longitude' => 13.143200],
            ['name' => 'ساحة جامعة طرابلس - القاطع أ', 'location_park' => 'بين كليتي الهندسة والعلوم', 'total_capacity' => 90, 'available_capacity' => 61, 'latitude' => 32.855400, 'longitude' => 13.204500],
            ['name' => 'ساحة مستشفى الخضراء والعيادات', 'location_park' => 'بجانب قسم الطوارئ والمدخل الرئيسي', 'total_capacity' => 45, 'available_capacity' => 10, 'latitude' => 32.850100, 'longitude' => 13.188000],
            ['name' => 'ساحة مجمع المهاري - الظهرة', 'location_park' => 'مجمع المهاري للتسوق - الظهرة', 'total_capacity' => 55, 'available_capacity' => 28, 'latitude' => 32.897800, 'longitude' => 13.211200],
            ['name' => 'ساحة النوفليين المركزية', 'location_park' => 'شارع النوفليين الرئيسي - بجوار مسجد البخاري', 'total_capacity' => 40, 'available_capacity' => 19, 'latitude' => 32.880500, 'longitude' => 13.215500],
            ['name' => 'ساحة زرقاء اليمامة - بن عاشور', 'location_park' => 'شارع بن عاشور الرئيسي - مقابل المصرف التجاري', 'total_capacity' => 50, 'available_capacity' => 27, 'latitude' => 32.878000, 'longitude' => 13.201000],
            ['name' => 'ساحة غوط الشعال التجارية', 'location_park' => 'شارع الغرب الرئيسي - بجوار مجمع غوط الشعال', 'total_capacity' => 60, 'available_capacity' => 34, 'latitude' => 32.864000, 'longitude' => 13.138000]
        ];

        $createdParkingIds = [];

        foreach ($parkingSeeds as $idx => $pData) {
            $assignedManagerId = $managerIds[$idx % count($managerIds)];
            
            $pId = DB::table('parkings')->insertGetId([
                'name' => $pData['name'],
                'location_park' => $pData['location_park'],
                'total_capacity' => $pData['total_capacity'],
                'available_capacity' => $pData['available_capacity'],
                'latitude' => $pData['latitude'],
                'longitude' => $pData['longitude'],
                'employee_id' => null,
                'manager_id' => $assignedManagerId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $createdParkingIds[] = $pId;
        }

        // 4. Create 25 Field Employees Assigned to Morning & Evening Shifts
        $employeeNames = [
            'أحمد علي المنصوري', 'محمد سالم الترهوني', 'عمر عبد اللطيف الورفلي',
            'طارق المبروك الغرياني', 'خالد مصطفى المقريف', 'عبد السلام الفيتوري',
            'أسامة بشير الزاوي', 'وليد خليفة الكاديكي', 'حمزة سعد المصراتي',
            'يوسف حسن القرقني', 'أيمن فرج السويحلي', 'مصطفى عمران البكوش',
            'مروان نوري القمودي', 'رمزي مفتاح الشريف', 'زياد الطاهر التاجوري',
            'سليمان رجب الغناي', 'فوزي محمود الصادق', 'بلقاسم فتحي التومي',
            'سالم إبراهيم الجازوي', 'عادل مفتاح السباعي', 'فراس بشير الفزاني',
            'إسماعيل عادل الخويلدي', 'شريف ناصر الدين السعداوي', 'مالك علي الكيلاني', 'أيوب عمر بن عيسى'
        ];

        $shiftRoles = ['الوردية الصباحية', 'الوردية المسائية'];
        $createdEmployeeIds = [];

        foreach ($employeeNames as $i => $empName) {
            $email = ($i === 0) ? 'employee@spotly.com' : "employee" . ($i + 1) . "@spotly.com";
            $phone = "09100000" . str_pad($i + 1, 2, '0', STR_PAD_LEFT);

            $empAccId = DB::table('accounts')->insertGetId([
                'name' => $empName,
                'email' => $email,
                'phone' => $phone,
                'password' => Hash::make('password123'),
                'role' => 'employee',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $assignedParkingId = $createdParkingIds[$i % count($createdParkingIds)];
            $assignedShiftRole = $shiftRoles[$i % count($shiftRoles)];

            $empId = DB::table('employees')->insertGetId([
                'account_id' => $empAccId,
                'parking_id' => $assignedParkingId,
                'shift_role' => $assignedShiftRole,
                'bank_account_number' => "LY1234567890123456" . str_pad($i + 1, 2, '0', STR_PAD_LEFT),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('parkings')->where('id', $assignedParkingId)->whereNull('employee_id')->update(['employee_id' => $empId]);
            $createdEmployeeIds[] = $empId;
        }

        // 5. Drivers: Active Drivers (40) + Blocked Drivers / Violators (6)
        $driverUserAccountIds = [];

        // 5a. Active Drivers
        for ($i = 1; $i <= 40; $i++) {
            $email = ($i === 1) ? 'user@spotly.com' : "user{$i}@spotly.com";
            $name = ($i === 1) ? 'السائق الرئيسي التجريبي' : "سائق موصول " . $i;
            $phone = "09300000" . str_pad($i, 2, '0', STR_PAD_LEFT);
            $plateNum = rand(5, 22) . "-" . (10000 + $i);

            $userAccountId = DB::table('accounts')->insertGetId([
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'password' => Hash::make('password123'),
                'role' => 'user',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('users')->insert([
                'account_id' => $userAccountId,
                'plate_number' => $plateNum,
                'status' => 'active',
                'fake_booking_count' => rand(0, 2),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Charged Wallet for Driver
            DB::table('wallets')->insert([
                'user_id' => $userAccountId,
                'balance' => rand(80, 500) . '.00',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $driverUserAccountIds[] = [
                'account_id' => $userAccountId,
                'plate_number' => $plateNum,
                'email' => $email
            ];
        }

        // 5b. Blocked Drivers (With 3+ violations for Manager Dashboard Unblock testing!)
        $blockedDriverNames = [
            'سائق مخالف (محظور 1)', 'سائق مخالف (محظور 2)', 'سائق مخالف (محظور 3)',
            'سائق مخالف (محظور 4)', 'سائق مخالف (محظور 5)', 'سائق مخالف (محظور 6)'
        ];

        foreach ($blockedDriverNames as $bIdx => $bdName) {
            $bdEmail = "blocked.user" . ($bIdx + 1) . "@spotly.com";
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

            $driverUserAccountIds[] = [
                'account_id' => $bdAccId,
                'plate_number' => $bdPlate,
                'email' => $bdEmail
            ];
        }

        // 6. Create 120+ Realistic Bookings
        $bookingStatuses = ['confirmed', 'cancelled'];
        $bookingTypes = ['initial', 'actual'];

        for ($b = 1; $b <= 120; $b++) {
            $driverItem = $driverUserAccountIds[array_rand($driverUserAccountIds)];
            $randomParkingId = $createdParkingIds[array_rand($createdParkingIds)];
            $status = $bookingStatuses[rand(0, 1)];
            $bType = $bookingTypes[rand(0, 1)];

            $startTime = now()->subHours(rand(1, 120));
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

        // 7. Create 50+ Employee Shift Attendance Logs
        $shiftNotes = [
            'مناوبة اعتيادية - حركة سير ممتازة ومتابعة حثيثة',
            'تم التفتيش الميداني وصيانة كشك التحصيل الإلكتروني',
            'مناوبة مسائية هادئة وتسليم صندوق الكاش للمشرف',
            'حضور وانصراف حسب الجدول الزمني ومطابقة الشواغر',
            'مراقبة البوابة الرئيسية وتوجيه السيارات الشاغرة'
        ];

        foreach ($createdEmployeeIds as $empIdItem) {
            // Active Shift (Clocked-In Now)
            DB::table('employee_shifts')->insert([
                'employee_id' => $empIdItem,
                'clock_in_at' => now()->subHours(rand(1, 4)),
                'clock_out_at' => null,
                'status' => 'active',
                'notes' => 'مناوبة ميدانية نشطة حالياً',
                'created_at' => now()->subHours(rand(1, 4)),
                'updated_at' => now(),
            ]);

            // Past Completed Shift
            $pastClockIn = now()->subDays(rand(1, 5))->subHours(rand(5, 10));
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

        // 8. Create Activity Cash Audit Logs
        $opTypes = ['entry', 'exit', 'recharge'];

        for ($c = 1; $c <= 45; $c++) {
            $empId = $createdEmployeeIds[array_rand($createdEmployeeIds)];
            $pId = $createdParkingIds[array_rand($createdParkingIds)];
            $drv = $driverUserAccountIds[array_rand($driverUserAccountIds)];
            $op = $opTypes[rand(0, 2)];
            $cashVal = ($op === 'recharge') ? (rand(2, 10) * 10.00) : (rand(1, 3) * 2.50);

            DB::table('activity_cash_audit_logs')->insert([
                'employee_id' => $empId,
                'parking_id' => $pId,
                'operation_type' => $op,
                'plate_number' => $drv['plate_number'],
                'cash_value' => $cashVal,
                'driver_account_id' => $drv['account_id'],
                'created_at' => now()->subHours(rand(1, 96)),
                'updated_at' => now(),
            ]);
        }

        // 9. Create Recharge Requests
        $rechargeStatuses = ['Pending', 'Approved', 'Rejected'];

        for ($r = 1; $r <= 20; $r++) {
            $drv = $driverUserAccountIds[array_rand($driverUserAccountIds)];
            $pId = $createdParkingIds[array_rand($createdParkingIds)];
            $rStatus = $rechargeStatuses[rand(0, 2)];

            DB::table('recharge_requests')->insert([
                'user_id' => $drv['account_id'],
                'parking_id' => $pId,
                'requested_points' => rand(20, 150),
                'receipt_file' => 'receipts/demo_receipt_' . rand(1, 5) . '.png',
                'status' => $rStatus,
                'created_at' => now()->subHours(rand(1, 48)),
                'updated_at' => now(),
            ]);
        }

        // 10. Create Notifications
        foreach ($driverUserAccountIds as $dItem) {
            DB::table('notifications')->insert([
                'user_id' => $dItem['account_id'],
                'message' => 'مرحباً بك في SpotLy! 🚗 تم تفعيل محفظتك وتوفير نقاط الحجز المباشر بنجاح.',
                'type' => 'welcome',
                'sent_to_email' => $dItem['email'],
                'sent_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('🎉 تمت إعادة زراعة كامل قاعدة بيانات SpotLy ببيانات وهمية واقعية وحسابات محظورة ونشطة!');
        $this->command->info('----------------------------------------------------');
        $this->command->info('🔑 بيانات الدخول المعتمدة (كلمة المرور الموحدة: password123)');
        $this->command->info('👑 المطور العام (Super Admin): developer@spotly.com');
        $this->command->info('👔 المدير الرئيسي (Active Manager): manager@spotly.com');
        $this->command->info('🚫 مدراء محظورون لاختبار فك الحظر: blocked.manager1@spotly.com');
        $this->command->info('👮 الموظف الميداني (Employee): employee@spotly.com');
        $this->command->info('🚗 السائق (User Driver): user@spotly.com');
        $this->command->info('🚫 سائقون محظورون (6 حسابات محظورة مع مخالفتين أو أكثر لاختبار فك الحظر)');
        $this->command->info('----------------------------------------------------');
        $this->command->info('📊 الإحصاءات الكلية المزروعة:');
        $this->command->info('- 12 ساحة مواقف تفاعلية في طرابلس.');
        $this->command->info('- 8 مدراء نشطين + 2 مدراء محظورين.');
        $this->command->info('- 25 موظف ميداني موزعين على الورديات الصباحية والمسائية.');
        $this->command->info('- 40 سائق نشط + 6 سائقين محظورين في قائمة المخالفين.');
        $this->command->info('- 120+ حجز مؤكد ومكتمل.');
        $this->command->info('- 50 سجل مناوبة وحضور وانصراف.');
        $this->command->info('- 45 سجل نشاط مالي وكاش.');
        $this->command->info('- 20 طلب شحن رصيد (Pending, Approved, Rejected).');
    }
}