<?php

namespace Database\Factories;

use App\Models\CourseMaterial;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class GeneratedQuizFactory extends Factory
{
    public function definition(): array
    {
        $material = CourseMaterial::factory()->create();
        
        return [
            'course_material_id' => $material->id,
            'material_version' => $material->version,
            'generated_by' => User::factory()->create()->id,
            'ai_provider' => 'gemini',
            'ai_model' => 'gemini-2.0-flash-exp',
            'question_count' => fake()->numberBetween(3, 10),
            'source_char_count' => fake()->numberBetween(500, 5000),
            'generation_time_ms' => fake()->numberBetween(1000, 5000),
        ];
    }
}
