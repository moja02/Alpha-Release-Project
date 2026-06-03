<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ParkingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $parkings = [
            [
                'name' => 'موقف ميدان الشهداء',
                'location_park' => 'بجانب السراي الحمراء - وسط البلد',
                'total_capacity' => 80,
                'available_capacity' => 80,
                'latitude' => 32.895456,
                'longitude' => 13.180324,
                'employee_id' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'موقف برج طرابلس',
                'location_park' => 'خلف البرج - شارع سبتمبر',
                'total_capacity' => 120,
                'available_capacity' => 120,
                'latitude' => 32.897120,
                'longitude' => 13.175110,
                'employee_id' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'موقف طريق الشط (الميناء)',
                'location_park' => 'مقابل ميناء طرابلس البحري',
                'total_capacity' => 150,
                'available_capacity' => 150,
                'latitude' => 32.901500,
                'longitude' => 13.189000,
                'employee_id' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'موقف ميدان الجزائر',
                'location_park' => 'بجوار مبنى البلدية والبريد',
                'total_capacity' => 60,
                'available_capacity' => 60,
                'latitude' => 32.893200,
                'longitude' => 13.190100,
                'employee_id' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'موقف حي الأندلس (الفروسية)',
                'location_park' => 'بجانب نادي الفروسية',
                'total_capacity' => 100,
                'available_capacity' => 100,
                'latitude' => 32.889000,
                'longitude' => 13.129000,
                'employee_id' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'موقف قرقارش (المصارف)',
                'location_park' => 'شارع قرقارش الرئيسي - خلف مصرف الأمان',
                'total_capacity' => 45,
                'available_capacity' => 45,
                'latitude' => 32.876200,
                'longitude' => 13.143200,
                'employee_id' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'موقف جامعة طرابلس (القاطع أ)',
                'location_park' => 'بين كليتي الهندسة والعلوم',
                'total_capacity' => 200,
                'available_capacity' => 200,
                'latitude' => 32.855400,
                'longitude' => 13.204500,
                'employee_id' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'موقف مستشفى الخضراء',
                'location_park' => 'بجانب قسم الطوارئ',
                'total_capacity' => 90,
                'available_capacity' => 90,
                'latitude' => 32.850100,
                'longitude' => 13.188000,
                'employee_id' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'موقف سوق المهاري',
                'location_park' => 'مجمع المهاري للتسوق - الظهرة',
                'total_capacity' => 75,
                'available_capacity' => 75,
                'latitude' => 32.897800,
                'longitude' => 13.211200,
                'employee_id' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'موقف نوفليين الرئيسي',
                'location_park' => 'شارع نوفليين الرئيسي - بجوار مسجد البخاري',
                'total_capacity' => 50,
                'available_capacity' => 50,
                'latitude' => 32.880500,
                'longitude' => 13.215500,
                'employee_id' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'موقف جزيرة غوط الشعال',
                'location_park' => 'بجوار محطة الوقود الرئيسية',
                'total_capacity' => 40,
                'available_capacity' => 40,
                'latitude' => 32.863300,
                'longitude' => 13.111200,
                'employee_id' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'موقف الهضبة الخضراء (الحديقة)',
                'location_park' => 'مقابل منتزه الهضبة الخضراء العائلي',
                'total_capacity' => 70,
                'available_capacity' => 70,
                'latitude' => 32.839000,
                'longitude' => 13.202000,
                'employee_id' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ];

        DB::table('parkings')->insert($parkings);

        $this->command->info('تمت إضافة المواقف الوهمية بنجاح! 🚗📍');
    }
}
