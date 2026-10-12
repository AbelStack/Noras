<?php

namespace Database\Factories;

use App\Models\GeneratedQuiz;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuizQuestionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'quiz_id' => GeneratedQuiz::factory(),
            'question_order' => 1,
            'question_text' => fake()->sentence() . '?',
            'explanation' => fake()->paragraph(),
        ];
    }
}
