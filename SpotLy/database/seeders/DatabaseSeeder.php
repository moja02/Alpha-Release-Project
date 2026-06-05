<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // تعطيل قيود المفتاح الأجنبي لتنظيف الجداول وإعادة زراعتها بشكل نظيف
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
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

        // 1. زراعة مطور باسم محمد
        $developerAccountId = DB::table('accounts')->insertGetId([
            'name' => 'محمد',
            'email' => 'developer@spotly.com',
            'phone' => '0910001122',
            'password' => Hash::make('password123'),
            'role' => 'developer',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. زراعة 5 مدراء
        $managerIds = [];
        for ($i = 1; $i <= 5; $i++) {
            $managerAccountId = DB::table('accounts')->insertGetId([
                'name' => "مدير " . $i,
                'email' => "manager{$i}@spotly.com",
                'phone' => "092000000{$i}",
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

            $managerIds[] = $managerId;
        }

        // 3. زراعة 10 موظفين
        $employeeIds = [];
        for ($i = 1; $i <= 10; $i++) {
            $employeeAccountId = DB::table('accounts')->insertGetId([
                'name' => "موظف " . $i,
                'email' => "employee{$i}@spotly.com",
                'phone' => "09100000" . str_pad($i, 2, '0', STR_PAD_LEFT),
                'password' => Hash::make('password123'),
                'role' => 'employee',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $employeeId = DB::table('employees')->insertGetId([
                'account_id' => $employeeAccountId,
                'bank_account_number' => "LY1234567890123456" . str_pad($i, 2, '0', STR_PAD_LEFT),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $employeeIds[] = $employeeId;
        }

        // 4. زراعة 20 مستخدم
        for ($i = 1; $i <= 20; $i++) {
            $userAccountId = DB::table('accounts')->insertGetId([
                'name' => "مستخدم " . $i,
                'email' => "user{$i}@spotly.com",
                'phone' => "09300000" . str_pad($i, 2, '0', STR_PAD_LEFT),
                'password' => Hash::make('password123'),
                'role' => 'user',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $userId = DB::table('users')->insertGetId([
                'account_id' => $userAccountId,
                'plate_number' => "5-" . (10000 + $i),
                'status' => 'active',
                'fake_booking_count' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // إنشاء محفظة مشحونة للمستخدم
            DB::table('wallets')->insert([
                'user_id' => $userAccountId, // يربط بـ accounts.id كما هو معتمد في المشروع
                'balance' => 100.00, // 100 نقطة مبدئية للتجربة
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 5. زراعة 10 ساحات
        // كل مدير يدير ساحتين (5 مدراء * 2 = 10 ساحات)
        // كل موظف مربوط بساحة واحدة (10 موظفين * 1 = 10 ساحات)
        $parkingNames = [
            'ساحة ميدان الشهداء',
            'ساحة برج طرابلس',
            'ساحة طريق الشط',
            'ساحة ميدان الجزائر',
            'ساحة حي الأندلس',
            'ساحة قرقارش',
            'ساحة جامعة طرابلس',
            'ساحة مستشفى الخضراء',
            'ساحة سوق المهاري',
            'ساحة نوفليين',
        ];

        $parkingLocations = [
            'بجانب السراي الحمراء - وسط البلد',
            'خلف البرج - شارع سبتمبر',
            'مقابل ميناء طرابلس البحري',
            'بجوار مبنى البلدية والبريد',
            'بجانب نادي الفروسية',
            'شارع قرقارش الرئيسي - خلف مصرف الأمان',
            'بين كليتي الهندسة والعلوم',
            'بجانب قسم الطوارئ',
            'مجمع المهاري للتسوق - الظهرة',
            'شارع نوفليين الرئيسي - بجوار مسجد البخاري',
        ];

        $latitudes = [32.895456, 32.897120, 32.901500, 32.893200, 32.889000, 32.876200, 32.855400, 32.850100, 32.897800, 32.880500];
        $longitudes = [13.180324, 13.175110, 13.189000, 13.190100, 13.129000, 13.143200, 13.204500, 13.188000, 13.211200, 13.215500];

        for ($i = 0; $i < 10; $i++) {
            // المدير المسؤول:
            // الساحات 0 و 1 للمدير 1 (الفهرس 0)
            // الساحات 2 و 3 للمدير 2 (الفهرس 1)
            // وهكذا...
            $managerIndex = floor($i / 2);
            $managerId = $managerIds[$managerIndex];

            // الموظف المسؤول:
            // الساحة $i للموظف $i
            $employeeId = $employeeIds[$i];

            DB::table('parkings')->insert([
                'name' => $parkingNames[$i],
                'location_park' => $parkingLocations[$i],
                'total_capacity' => 50 + ($i * 10), // سعات متفاوتة للتجربة
                'available_capacity' => 50 + ($i * 10),
                'latitude' => $latitudes[$i],
                'longitude' => $longitudes[$i],
                'employee_id' => $employeeId,
                'manager_id' => $managerId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        //كلمة المرور الموحدة لجميع الحسابات لسهولة التجربة هي password123.
        $this->command->info('تمت إعادة تهيئة وزراعة بيانات SpotLy بنجاح! ');
        $this->command->info('- مطور واحد باسم محمد (developer@spotly.com)');
        $this->command->info('- 5 مدراء (كل مدير يدير ساحتين)');
        $this->command->info('- 10 موظفين (كل موظف مربوط بساحة)');
        $this->command->info('- 20 مستخدم مع محافظ مشحونة (100 نقطة لكل مستخدم)');
        $this->command->info('- 10 ساحات وقوف سيارات');
    }
}