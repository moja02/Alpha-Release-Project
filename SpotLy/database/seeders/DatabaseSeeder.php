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
        // 1. زراعة الحساب الأساسي للموظف (في جدول accounts)
        $employeeAccountId = DB::table('accounts')->insertGetId([
            'name' => 'أحمد الموظف',
            'email' => 'employee@spotly.com',
            'phone' => '0912345678',
            'password' => Hash::make('password123'), // كلمة السر الموحدة للتجربة
            'role' => 'employee',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // تخصيص ملف الموظف وتوليد الـ ID الخاص به
        $employeeId = DB::table('employees')->insertGetId([
            'account_id' => $employeeAccountId,
            'bank_account_number' => 'LY123456789012345679',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. زراعة ساحات الوقوف (Parkings) وربطها بالموظف
        $parking1Id = DB::table('parkings')->insertGetId([
            'name' => 'الساحة الشمالية (A)',
            'location_park' => 'بجوار البوابة الرئيسية',
            'total_capacity' => 50,
            'available_capacity' => 50,
            'employee_id' => $employeeId, // <-- التعديل الجديد: ربط الساحة بالموظف أحمد
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // إضافة ساحة أخرى بدون موظف لتجربة النظام
        DB::table('parkings')->insert([
            'name' => 'الساحة الجنوبية (B)',
            'location_park' => 'خلف مبنى الإدارة',
            'total_capacity' => 30,
            'available_capacity' => 30,
            'employee_id' => null, 
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. زراعة الحساب الأساسي للسائق (Driver/User)
        $driverAccountId = DB::table('accounts')->insertGetId([
            'name' => 'محمد السائق',
            'email' => 'driver@spotly.com',
            'phone' => '0921234567',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // تخصيص ملف المستخدم (السائق)
        DB::table('users')->insert([
            'account_id' => $driverAccountId,
            'plate_number' => '12345-5',
            'fake_booking_count' => 0,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4. إنشاء محفظة مشحونة للسائق لكي يبدأ الحجز فوراً
        DB::table('wallets')->insert([
            'user_id' => $driverAccountId,
            'balance' => 50.00, // 50 نقطة لتجربة الحجوزات
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info('تمت زراعة بيانات SpotLy وتطبيق الهيكلة الجديدة (الموظف-الموقف) بنجاح! 🚗✨');
    }
}