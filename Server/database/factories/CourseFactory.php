<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->bothify('???-###'),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'credit_hours' => fake()->numberBetween(2, 4),
            'is_active' => true,
        ];
    }
}
