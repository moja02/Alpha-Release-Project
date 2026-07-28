<?php

namespace Database\Factories;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        $status = $this->faker->randomElement(['active', 'completed', 'cancelled']);
        $startTime = now()->subMinutes($this->faker->numberBetween(10, 180));

        return [
            'account_id' => 1,
            'parking_id' => 1,
            'spot_number' => $this->faker->randomElement(['A-', 'B-', 'C-']) . $this->faker->numberBetween(1, 40),
            'booking_type' => $this->faker->randomElement(['initial', 'actual']),
            'status' => $status,
            'start_time' => $startTime,
            'end_time' => $status === 'completed' ? (clone $startTime)->addHours($this->faker->numberBetween(1, 4)) : null,
            'created_at' => $startTime,
            'updated_at' => now(),
        ];
    }
}
