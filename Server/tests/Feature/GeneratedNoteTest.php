<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseMaterial;
use App\Models\CourseOffering;
use App\Models\GeneratedNote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GeneratedNoteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private');

        // Configure AI provider for tests
        config([
            'services.ai.provider' => 'gemini',
            'services.ai.gemini.api_key' => 'test-gemini-key',
            'services.ai.gemini.model' => 'gemini-2.0-flash-exp',
            'services.ai.gemini.max_chars' => 10000,
        ]);

        // Mock HTTP responses for AI providers
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    ['content' => ['parts' => [['text' => 'These are AI-generated notes from the PDF content.']]]]
                ]
            ], 200),
        ]);
    }

    #[Test]
    public function unauthenticated_user_cannot_generate_notes()
    {
        $response = $this->postJson('/api/v1/notes/generate/1');

        $response->assertStatus(401);
    }

    #[Test]
    public function unapproved_user_cannot_generate_notes()
    {
        $user = User::factory()->unapproved()->create();

        $material = CourseMaterial::factory()->create(['status' => 'published']);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/notes/generate/{$material->id}");

        $response->assertStatus(403);
    }

    #[Test]
    public function user_can_generate_notes_for_published_material()
    {
        $user = User::factory()->create(['account_status' => 'approved']);

        $course = Course::factory()->create();
        $offering = CourseOffering::factory()->create(['course_id' => $course->id]);

        // Create a sample PDF file
        $pdfContent = file_get_contents(base_path('tests/fixtures/sample.pdf'));
        Storage::disk('private')->put('materials/test.pdf', $pdfContent);

        $material = CourseMaterial::factory()->create([
            'course_offering_id' => $offering->id,
            'status' => 'published',
            'file_path' => 'materials/test.pdf',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/notes/generate/{$material->id}");

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Notes generated successfully',
            ]);

        $this->assertDatabaseHas('generated_notes', [
            'course_material_id' => $material->id,
            'generated_by' => $user->id,
            'ai_provider' => 'gemini',
        ]);
    }

    #[Test]
    public function user_can_regenerate_existing_notes()
    {
        $user = User::factory()->create(['account_status' => 'approved']);

        $course = Course::factory()->create();
        $offering = CourseOffering::factory()->create(['course_id' => $course->id]);

        $pdfContent = file_get_contents(base_path('tests/fixtures/sample.pdf'));
        Storage::disk('private')->put('materials/test.pdf', $pdfContent);

        $material = CourseMaterial::factory()->create([
            'course_offering_id' => $offering->id,
            'status' => 'published',
            'file_path' => 'materials/test.pdf',
        ]);

        // Generate first note
        $existingNote = GeneratedNote::create([
            'course_material_id' => $material->id,
            'material_version' => $material->version,
            'generated_by' => $user->id,
            'content' => 'Old notes',
            'ai_provider' => 'gemini',
            'ai_model' => 'old-model',
            'source_char_count' => 100,
            'notes_char_count' => 50,
            'generation_time_ms' => 1000,
        ]);

        // Regenerate with flag
        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/notes/generate/{$material->id}", [
                'regenerate' => true,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'note' => [
                        'id' => $existingNote->id,
                        'content' => 'These are AI-generated notes from the PDF content.',
                    ],
                ],
            ]);
    }

    #[Test]
    public function user_can_list_their_generated_notes()
    {
        $user = User::factory()->create(['account_status' => 'approved']);

        $course = Course::factory()->create();
        $offering = CourseOffering::factory()->create(['course_id' => $course->id]);

        $material = CourseMaterial::factory()->create([
            'course_offering_id' => $offering->id,
            'status' => 'published',
        ]);

        // Create multiple notes
        GeneratedNote::factory()->count(3)->create([
            'course_material_id' => $material->id,
            'generated_by' => $user->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/notes');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonCount(3, 'data.notes');
    }

    #[Test]
    public function user_can_view_specific_note()
    {
        $user = User::factory()->create(['account_status' => 'approved']);

        $course = Course::factory()->create();
        $offering = CourseOffering::factory()->create(['course_id' => $course->id]);

        $material = CourseMaterial::factory()->create([
            'course_offering_id' => $offering->id,
            'status' => 'published',
        ]);

        $note = GeneratedNote::factory()->create([
            'course_material_id' => $material->id,
            'generated_by' => $user->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/notes/{$note->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'note' => [
                        'id' => $note->id,
                    ],
                ],
            ]);
    }

    #[Test]
    public function user_cannot_view_other_users_notes()
    {
        $user1 = User::factory()->create(['account_status' => 'approved']);
        $user2 = User::factory()->create(['account_status' => 'approved']);

        $material = CourseMaterial::factory()->create(['status' => 'published']);

        $note = GeneratedNote::factory()->create([
            'course_material_id' => $material->id,
            'generated_by' => $user2->id,
        ]);

        $response = $this->actingAs($user1, 'sanctum')
            ->getJson("/api/v1/notes/{$note->id}");

        $response->assertStatus(404);
    }

    #[Test]
    public function user_can_delete_their_own_note()
    {
        $user = User::factory()->create(['account_status' => 'approved']);
        $material = CourseMaterial::factory()->create(['status' => 'published']);

        $note = GeneratedNote::factory()->create([
            'course_material_id' => $material->id,
            'generated_by' => $user->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/notes/{$note->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Note deleted successfully',
            ]);

        $this->assertDatabaseMissing('generated_notes', [
            'id' => $note->id,
        ]);
    }

    #[Test]
    public function user_cannot_delete_other_users_notes()
    {
        $user1 = User::factory()->create(['account_status' => 'approved']);
        $user2 = User::factory()->create(['account_status' => 'approved']);

        $material = CourseMaterial::factory()->create(['status' => 'published']);

        $note = GeneratedNote::factory()->create([
            'course_material_id' => $material->id,
            'generated_by' => $user2->id,
        ]);

        $response = $this->actingAs($user1, 'sanctum')
            ->deleteJson("/api/v1/notes/{$note->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('generated_notes', [
            'id' => $note->id,
        ]);
    }

    #[Test]
    public function notes_list_is_paginated()
    {
        $user = User::factory()->create(['account_status' => 'approved']);
        
        // Create 25 different materials
        $materials = CourseMaterial::factory()->count(25)->create(['status' => 'published']);

        // Create one note for each material
        foreach ($materials as $material) {
            GeneratedNote::factory()->create([
                'course_material_id' => $material->id,
                'generated_by' => $user->id,
            ]);
        }

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/notes');

        $response->assertStatus(200)
            ->assertJsonCount(20, 'data.notes')
            ->assertJson([
                'data' => [
                    'pagination' => [
                        'current_page' => 1,
                        'per_page' => 20,
                        'total' => 25,
                        'last_page' => 2,
                    ],
                ],
            ]);
    }
}
