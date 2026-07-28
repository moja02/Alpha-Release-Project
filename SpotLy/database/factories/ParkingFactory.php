<?php

namespace Database\Factories;

use App\Models\Parking;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Parking>
 */
class ParkingFactory extends Factory
{
    protected $model = Parking::class;

    public function definition(): array
    {
        $parkingNames = [
            'ساحة المدار - طريق الشط',
            'موقف برج طرابلس وذات العماد',
            'ساحة الفندق الكبير',
            'موقف شارع عمر المختار',
            'ساحة كشلاف - حي الأندلس',
            'موقف ميدان الشهداء التفاعلي',
            'ساحة سوق الثلاثاء المركزي',
            'موقف جامعة طرابلس - القاطع أ'
        ];

        $totalCapacity = $this->faker->numberBetween(30, 60);

        return [
            'name' => $this->faker->randomElement($parkingNames) . ' (' . $this->faker->numberBetween(1, 99) . ')',
            'location_park' => 'طرابلس، ' . $this->faker->streetName(),
            'total_capacity' => $totalCapacity,
            'available_capacity' => $this->faker->numberBetween(5, $totalCapacity),
            'latitude' => 32.8800 + ($this->faker->randomFloat(4, 0, 200) / 10000),
            'longitude' => 13.1800 + ($this->faker->randomFloat(4, 0, 200) / 10000),
            'manager_id' => null,
            'employee_id' => null,
        ];
    }
}
