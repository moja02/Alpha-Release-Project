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
        // 1. زراعة ساحات الوقوف (Parkings)
        $parking1Id = DB::table('parkings')->insertGetId([
            'name' => 'الساحة الرئيسية - طرابلس',
            'location' => 'وسط المدينة - ميدان الشهداء',
            'total_capacity' => 50,
            'available_capacity' => 50,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $parking2Id = DB::table('parkings')->insertGetId([
            'name' => 'ساحة الجامعة',
            'location' => 'بجوار كلية الهندسة',
            'total_capacity' => 30,
            'available_capacity' => 30,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. زراعة حساب الموظف الميداني (Employee)
        $employeeAccountId = DB::table('accounts')->insertGetId([
            'email' => 'employee@spotly.com',
            'password' => Hash::make('password123'), // كلمة السر الموحدة للتجربة
            'role' => 'employee',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('employees')->insert([
            'account_id' => $employeeAccountId,
            'name' => 'أحمد الموظف',
            'phone' => '0912345678',
            'parking_id' => $parking1Id, // تعيينه للساحة الرئيسية
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. زراعة حساب السائق (Driver/User)
        $driverAccountId = DB::table('accounts')->insertGetId([
            'email' => 'driver@spotly.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->insert([
            'account_id' => $driverAccountId,
            'name' => 'محمد السائق',
            'phone' => '0921234567',
            'plate_number' => '12345-5',
            'fake_booking_count' => 0,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4. إنشاء محفظة مشحونة للسائق لكي يبدأ الحجز فوراً
        DB::table('wallets')->insert([
            'user_id' => $driverAccountId,
            'balance' => 50.00, // 50 نقطة كافية لعدة حجوزات
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info('تمت زراعة بيانات SpotLy بنجاح! 🚗✨');
    }
}