<?php

namespace Database\Factories;

use App\Models\CourseMaterial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CourseMaterial>
 */
class CourseMaterialFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $storedFilename = \Illuminate\Support\Str::uuid() . '.pdf';
        
        return [
            'course_offering_id' => \App\Models\CourseOffering::factory(),
            'uploaded_by' => \App\Models\User::factory()->create(['role' => 'admin']),
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'original_filename' => fake()->word() . '.pdf',
            'stored_filename' => $storedFilename,
            'file_path' => 'course-materials/' . $storedFilename,
            'file_size' => fake()->numberBetween(100000, 5000000),
            'mime_type' => 'application/pdf',
            'status' => 'draft',
            'version' => 1,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
        ]);
    }
}
