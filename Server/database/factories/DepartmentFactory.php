<?php

namespace Database\Factories;

use App\Models\University;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'university_id' => University::factory(),
            'name' => fake()->words(2, true) . ' Department',
            'code' => fake()->bothify('???##'),
            'is_active' => true,
        ];
    }
}
