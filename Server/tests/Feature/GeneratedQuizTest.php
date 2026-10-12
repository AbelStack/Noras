<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseMaterial;
use App\Models\CourseOffering;
use App\Models\GeneratedQuiz;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GeneratedQuizTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private');

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
                    ['content' => ['parts' => [['text' => json_encode([
                        'questions' => [
                            [
                                'question' => 'What is the primary topic covered in this material?',
                                'options' => ['Topic A', 'Topic B', 'Topic C', 'Topic D'],
                                'correct_index' => 1,
                                'explanation' => 'Based on the source material, Topic B is the primary focus.'
                            ],
                            [
                                'question' => 'Which concept is most important?',
                                'options' => ['Concept 1', 'Concept 2', 'Concept 3', 'Concept 4'],
                                'correct_index' => 0,
                                'explanation' => 'The material emphasizes Concept 1 throughout.'
                            ],
                        ]
                    ])]]]]
                ]
            ], 200),
        ]);
        
        // Mock PdfTextExtractor
        $this->mock(\App\Services\PdfTextExtractor::class, function ($mock) {
            $mock->shouldReceive('extractText')
                ->andReturn('Sample course material text for quiz generation. This contains sufficient content to create meaningful quiz questions about the subject matter.');
            $mock->shouldReceive('getCharacterCount')
                ->andReturn(150);
        });
    }

    #[Test]
    public function unauthenticated_user_cannot_generate_quiz()
    {
        $response = $this->postJson('/api/v1/quizzes/generate/1');

        $response->assertStatus(401);
    }

    #[Test]
    public function unapproved_user_cannot_generate_quiz()
    {
        $user = User::factory()->unapproved()->create();
        $material = CourseMaterial::factory()->create(['status' => 'published']);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/quizzes/generate/{$material->id}");

        $response->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'message' => 'Account not approved. Please wait for admin approval.',
            ]);
    }

    #[Test]
    public function user_can_generate_quiz_for_published_material()
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

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/quizzes/generate/{$material->id}", [
                'question_count' => 2,
                'options_per_question' => 4,
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Quiz generated successfully',
            ]);

        $this->assertDatabaseHas('generated_quizzes', [
            'course_material_id' => $material->id,
            'generated_by' => $user->id,
            'question_count' => 2,
        ]);

        $quiz = GeneratedQuiz::where('course_material_id', $material->id)->first();
        $this->assertCount(2, $quiz->questions);
        
        foreach ($quiz->questions as $question) {
            $this->assertCount(4, $question->options);
            $correctOptions = $question->options->where('is_correct', true);
            $this->assertCount(1, $correctOptions, 'Each question should have exactly one correct option');
        }
    }

    #[Test]
    public function quiz_generation_validates_question_count()
    {
        $user = User::factory()->create(['account_status' => 'approved']);
        $material = CourseMaterial::factory()->create(['status' => 'published']);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/quizzes/generate/{$material->id}", [
                'question_count' => 50, // Exceeds max
            ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function quiz_generation_validates_options_count()
    {
        $user = User::factory()->create(['account_status' => 'approved']);
        $material = CourseMaterial::factory()->create(['status' => 'published']);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/quizzes/generate/{$material->id}", [
                'options_per_question' => 10, // Exceeds max
            ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function user_can_list_their_generated_quizzes()
    {
        $user = User::factory()->create(['account_status' => 'approved']);
        $otherUser = User::factory()->create(['account_status' => 'approved']);

        $material1 = CourseMaterial::factory()->create();
        $material2 = CourseMaterial::factory()->create();
        $material3 = CourseMaterial::factory()->create();

        GeneratedQuiz::factory()->create([
            'course_material_id' => $material1->id,
            'material_version' => $material1->version,
            'generated_by' => $user->id
        ]);
        GeneratedQuiz::factory()->create([
            'course_material_id' => $material2->id,
            'material_version' => $material2->version,
            'generated_by' => $user->id
        ]);
        GeneratedQuiz::factory()->create([
            'course_material_id' => $material3->id,
            'material_version' => $material3->version,
            'generated_by' => $otherUser->id
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/quizzes');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.quizzes');
    }

    #[Test]
    public function user_can_view_specific_quiz_with_questions()
    {
        $user = User::factory()->create(['account_status' => 'approved']);
        $material = CourseMaterial::factory()->create();

        $quiz = GeneratedQuiz::factory()->create([
            'course_material_id' => $material->id,
            'material_version' => $material->version,
            'generated_by' => $user->id,
            'question_count' => 3,
        ]);

        for ($i = 1; $i <= 3; $i++) {
            $question = $quiz->questions()->create([
                'question_order' => $i,
                'question_text' => "Question {$i}?",
                'explanation' => "Explanation for question {$i}",
            ]);

            for ($j = 1; $j <= 4; $j++) {
                $question->options()->create([
                    'option_order' => $j,
                    'option_text' => "Option {$j}",
                    'is_correct' => $j === 1,
                ]);
            }
        }

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/quizzes/{$quiz->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'quiz' => ['id', 'question_count'],
                    'material' => ['id', 'title', 'version'],
                    'questions' => [
                        '*' => [
                            'id',
                            'question_order',
                            'question_text',
                            'explanation',
                            'options' => [
                                '*' => ['id', 'option_order', 'option_text', 'is_correct']
                            ]
                        ]
                    ]
                ]
            ])
            ->assertJsonCount(3, 'data.questions');
    }

    #[Test]
    public function user_cannot_view_other_users_quiz()
    {
        $user = User::factory()->create(['account_status' => 'approved']);
        $otherUser = User::factory()->create(['account_status' => 'approved']);
        
        $material = CourseMaterial::factory()->create();
        $quiz = GeneratedQuiz::factory()->create([
            'course_material_id' => $material->id,
            'material_version' => $material->version,
            'generated_by' => $otherUser->id
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/quizzes/{$quiz->id}");

        $response->assertStatus(404);
    }

    #[Test]
    public function user_can_delete_their_own_quiz()
    {
        $user = User::factory()->create(['account_status' => 'approved']);
        $material = CourseMaterial::factory()->create();
        
        $quiz = GeneratedQuiz::factory()->create([
            'course_material_id' => $material->id,
            'material_version' => $material->version,
            'generated_by' => $user->id
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/quizzes/{$quiz->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Quiz deleted successfully',
            ]);

        $this->assertDatabaseMissing('generated_quizzes', ['id' => $quiz->id]);
    }

    #[Test]
    public function user_cannot_delete_other_users_quiz()
    {
        $user = User::factory()->create(['account_status' => 'approved']);
        $otherUser = User::factory()->create(['account_status' => 'approved']);
        
        $material = CourseMaterial::factory()->create();
        $quiz = GeneratedQuiz::factory()->create([
            'course_material_id' => $material->id,
            'material_version' => $material->version,
            'generated_by' => $otherUser->id
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/quizzes/{$quiz->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('generated_quizzes', ['id' => $quiz->id]);
    }

    #[Test]
    public function quizzes_list_is_paginated()
    {
        $user = User::factory()->create(['account_status' => 'approved']);

        for ($i = 0; $i < 25; $i++) {
            $material = CourseMaterial::factory()->create();
            GeneratedQuiz::factory()->create([
                'course_material_id' => $material->id,
                'material_version' => $material->version,
                'generated_by' => $user->id
            ]);
        }

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/quizzes?per_page=10');

        $response->assertStatus(200)
            ->assertJsonCount(10, 'data.quizzes')
            ->assertJsonPath('data.pagination.total', 25)
            ->assertJsonPath('data.pagination.per_page', 10);
    }

    #[Test]
    public function quiz_generation_tracks_material_version()
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
            'version' => 1,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/quizzes/generate/{$material->id}", [
                'question_count' => 2,
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('generated_quizzes', [
            'course_material_id' => $material->id,
            'material_version' => 1,
            'generated_by' => $user->id,
        ]);
    }

    #[Test]
    public function quiz_generation_fails_for_unpublished_material()
    {
        $user = User::factory()->create(['account_status' => 'approved']);
        $material = CourseMaterial::factory()->create(['status' => 'draft']);

        // Mock PdfTextExtractor to throw exception for unpublished material
        $this->mock(\App\Services\PdfTextExtractor::class, function ($mock) {
            $mock->shouldReceive('extractText')
                ->andThrow(new \Exception('Cannot extract text from unpublished material'));
            $mock->shouldReceive('getCharacterCount')
                ->andReturn(150);
        });

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/quizzes/generate/{$material->id}");

        $response->assertStatus(500)
            ->assertJsonPath('success', false);
    }

    #[Test]
    public function quiz_generation_enforces_rate_limiting()
    {
        $user = User::factory()->create(['account_status' => 'approved']);
        $materials = CourseMaterial::factory()->count(12)->create(['status' => 'published']);

        // Make 10 requests (should succeed)
        for ($i = 0; $i < 10; $i++) {
            $this->actingAs($user, 'sanctum')
                ->postJson("/api/v1/quizzes/generate/{$materials[$i]->id}");
        }

        // 11th request should be rate limited
        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/quizzes/generate/{$materials[10]->id}");

        $response->assertStatus(429);
    }
}
