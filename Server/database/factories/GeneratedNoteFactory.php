<?php

namespace Database\Factories;

use App\Models\CourseMaterial;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GeneratedNote>
 */
class GeneratedNoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $material = CourseMaterial::factory()->create();
        
        return [
            'course_material_id' => $material->id,
            'material_version' => $material->version,
            'generated_by' => User::factory(),
            'content' => fake()->paragraphs(5, true),
            'ai_provider' => fake()->randomElement(['gemini', 'groq']),
            'ai_model' => fake()->randomElement(['gemini-2.0-flash-exp', 'llama-3.3-70b-versatile']),
            'source_char_count' => fake()->numberBetween(1000, 50000),
            'notes_char_count' => fake()->numberBetween(500, 10000),
            'generation_time_ms' => fake()->numberBetween(2000, 30000),
        ];
    }
}
