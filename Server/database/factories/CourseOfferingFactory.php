<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseOfferingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'department_id' => Department::factory(),
            'academic_year' => 2026,
            'study_year' => fake()->numberBetween(1, 4),
            'semester' => fake()->randomElement(['1', '2']),
            'is_active' => true,
        ];
    }
}
