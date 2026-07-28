<?php

namespace Database\Factories;

use App\Models\EmployeeShift;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmployeeShift>
 */
class EmployeeShiftFactory extends Factory
{
    protected $model = EmployeeShift::class;

    public function definition(): array
    {
        $status = $this->faker->randomElement(['active', 'completed']);
        $clockIn = now()->subHours($this->faker->numberBetween(1, 8));

        return [
            'employee_id' => 1,
            'clock_in_at' => $clockIn,
            'clock_out_at' => $status === 'completed' ? (clone $clockIn)->addHours($this->faker->numberBetween(4, 8)) : null,
            'status' => $status,
            'notes' => $this->faker->randomElement([
                'مناوبة اعتيادية - حركة سير ممتازة',
                'تم التفتيش الميداني وصيانة كشك الدفع',
                'مناوبة مسائية هادئة مع تسليم الصندوق',
                'حضور وانصراف حسب الجدول الزمني'
            ]),
            'created_at' => $clockIn,
            'updated_at' => now(),
        ];
    }
}
