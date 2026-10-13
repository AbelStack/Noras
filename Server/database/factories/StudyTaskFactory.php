<?php

namespace Database\Factories;

use App\Models\StudyTask;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudyTaskFactory extends Factory
{
    protected $model = StudyTask::class;

    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'title' => fake()->sentence(),
            'description' => fake()->optional()->paragraph(),
            'completed' => false,
            'due_date' => fake()->optional()->dateTimeBetween('now', '+30 days'),
            'completed_at' => null,
            'course_material_id' => null,
            'generated_note_id' => null,
            'generated_quiz_id' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'completed' => true,
            'completed_at' => now(),
        ]);
    }
}
