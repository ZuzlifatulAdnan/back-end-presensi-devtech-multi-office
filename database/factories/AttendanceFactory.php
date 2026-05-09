<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attendance>
 */
class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generate time_in between 7:00-9:00 AM
        $timeIn = $this->faker->dateTimeBetween('07:00:00', '09:00:00')->format('H:i:s');

        // Generate time_out between 4:00-6:00 PM (8-10 hours after time_in)
        $timeOut = $this->faker->dateTimeBetween('16:00:00', '18:00:00')->format('H:i:s');

        return [
            'user_id' => \App\Models\User::factory(),
            'date' => $this->faker->dateTimeThisMonth()->format('Y-m-d'),
            'time_in' => $timeIn,
            'time_out' => $timeOut,
            'latlon_in' => $this->faker->latitude().','.$this->faker->longitude(),
            'latlon_out' => $this->faker->latitude().','.$this->faker->longitude(),
        ];
    }
}
