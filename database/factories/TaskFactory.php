<?php

namespace Database\Factories;

use App\Models\Location;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->jobTitle,
            'location_id' => Location::factory(),
            'is_available' => $this->faker->boolean,
            'created_by' => 1,
            'created_at' => now(),
        ];
    }
}
