<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class UniversityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true) . ' University',
            'code' => fake()->unique()->bothify('??###'),
            'is_active' => true,
        ];
    }
}
