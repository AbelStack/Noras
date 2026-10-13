<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\StudyTask;
use App\Models\Bookmark;
use App\Models\CourseMaterial;
use App\Models\GeneratedNote;
use App\Models\GeneratedQuiz;
use App\Models\University;
use App\Models\Department;
use App\Models\Course;
use App\Models\CourseOffering;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LearningHistoryControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $approvedStudent;
    private User $otherStudent;
    private CourseMaterial $material;

    protected function setUp(): void
    {
        parent::setUp();

        $this->approvedStudent = User::factory()->create([
            'role' => 'student',
            'account_status' => 'approved',
        ]);

        $this->otherStudent = User::factory()->create([
            'role' => 'student',
            'account_status' => 'approved',
        ]);

        $university = University::factory()->create();
        $department = Department::factory()->create(['university_id' => $university->id]);
        $course = Course::factory()->create();
        $offering = CourseOffering::factory()->create([
            'course_id' => $course->id,
            'department_id' => $department->id,
        ]);
        $this->material = CourseMaterial::factory()->create([
            'course_offering_id' => $offering->id,
            'status' => 'published',
        ]);
    }

    public function test_unauthenticated_user_cannot_access_learning_history()
    {
        $this->getJson('/api/v1/learning/history')->assertUnauthorized();
        $this->getJson('/api/v1/learning/progress')->assertUnauthorized();
    }

    public function test_student_can_view_empty_learning_history()
    {
        $response = $this->actingAs($this->approvedStudent)
            ->getJson('/api/v1/learning/history');

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [],
                'pagination' => [
                    'current_page' => 1,
                    'total' => 0,
                ],
            ]);
    }

    public function test_learning_history_includes_generated_notes()
    {
        $note = GeneratedNote::factory()->create([
            'course_material_id' => $this->material->id,
            'generated_by' => $this->approvedStudent->id,
        ]);

        $response = $this->actingAs($this->approvedStudent)
            ->getJson('/api/v1/learning/history');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.type', 'note')
            ->assertJsonPath('data.0.id', $note->id)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'type', 'course_material_id', 'course_material', 'created_at'],
                ],
            ]);
    }

    public function test_learning_history_includes_generated_quizzes()
    {
        $quiz = GeneratedQuiz::factory()->create([
            'course_material_id' => $this->material->id,
            'generated_by' => $this->approvedStudent->id,
        ]);

        $response = $this->actingAs($this->approvedStudent)
            ->getJson('/api/v1/learning/history');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.type', 'quiz')
            ->assertJsonPath('data.0.id', $quiz->id);
    }

    public function test_learning_history_combines_notes_and_quizzes()
    {
        for ($i = 0; $i < 2; $i++) {
            $material = CourseMaterial::factory()->create([
                'course_offering_id' => $this->material->course_offering_id,
                'status' => 'published',
            ]);
            GeneratedNote::factory()->create([
                'course_material_id' => $material->id,
                'generated_by' => $this->approvedStudent->id,
            ]);
        }

        for ($i = 0; $i < 3; $i++) {
            $material = CourseMaterial::factory()->create([
                'course_offering_id' => $this->material->course_offering_id,
                'status' => 'published',
            ]);
            GeneratedQuiz::factory()->create([
                'course_material_id' => $material->id,
                'generated_by' => $this->approvedStudent->id,
            ]);
        }

        $response = $this->actingAs($this->approvedStudent)
            ->getJson('/api/v1/learning/history');

        $response->assertOk()
            ->assertJsonCount(5, 'data');
    }

    public function test_learning_history_is_sorted_by_created_at_desc()
    {
        $oldNote = GeneratedNote::factory()->create([
            'course_material_id' => $this->material->id,
            'generated_by' => $this->approvedStudent->id,
            'created_at' => now()->subDays(5),
        ]);

        $recentQuiz = GeneratedQuiz::factory()->create([
            'course_material_id' => $this->material->id,
            'generated_by' => $this->approvedStudent->id,
            'created_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($this->approvedStudent)
            ->getJson('/api/v1/learning/history');

        $response->assertOk()
            ->assertJsonPath('data.0.type', 'quiz') // Most recent first
            ->assertJsonPath('data.1.type', 'note');
    }

    public function test_learning_history_only_shows_users_own_items()
    {
        GeneratedNote::factory()->create([
            'course_material_id' => $this->material->id,
            'generated_by' => $this->approvedStudent->id,
        ]);

        GeneratedNote::factory()->create([
            'course_material_id' => $this->material->id,
            'generated_by' => $this->otherStudent->id,
        ]);

        $response = $this->actingAs($this->approvedStudent)
            ->getJson('/api/v1/learning/history');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_learning_history_respects_pagination()
    {
        for ($i = 0; $i < 15; $i++) {
            $material = CourseMaterial::factory()->create([
                'course_offering_id' => $this->material->course_offering_id,
                'status' => 'published',
            ]);
            GeneratedNote::factory()->create([
                'course_material_id' => $material->id,
                'generated_by' => $this->approvedStudent->id,
            ]);
        }

        $response = $this->actingAs($this->approvedStudent)
            ->getJson('/api/v1/learning/history?per_page=10');

        $response->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('pagination.per_page', 10)
            ->assertJsonPath('pagination.total', 15)
            ->assertJsonPath('pagination.last_page', 2);
    }

    public function test_student_can_view_empty_progress_summary()
    {
        $response = $this->actingAs($this->approvedStudent)
            ->getJson('/api/v1/learning/progress');

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'notes_generated' => 0,
                    'quizzes_generated' => 0,
                    'active_tasks' => 0,
                    'completed_tasks' => 0,
                    'bookmarks' => 0,
                ],
            ]);
    }

    public function test_progress_summary_counts_notes_generated()
    {
        for ($i = 0; $i < 3; $i++) {
            $material = CourseMaterial::factory()->create([
                'course_offering_id' => $this->material->course_offering_id,
                'status' => 'published',
            ]);
            GeneratedNote::factory()->create([
                'course_material_id' => $material->id,
                'generated_by' => $this->approvedStudent->id,
            ]);
        }

        $response = $this->actingAs($this->approvedStudent)
            ->getJson('/api/v1/learning/progress');

        $response->assertOk()
            ->assertJsonPath('data.notes_generated', 3);
    }

    public function test_progress_summary_counts_quizzes_generated()
    {
        GeneratedQuiz::factory()->count(5)->create([
            'course_material_id' => $this->material->id,
            'generated_by' => $this->approvedStudent->id,
        ]);

        $response = $this->actingAs($this->approvedStudent)
            ->getJson('/api/v1/learning/progress');

        $response->assertOk()
            ->assertJsonPath('data.quizzes_generated', 5);
    }

    public function test_progress_summary_counts_active_tasks()
    {
        StudyTask::factory()->count(4)->create([
            'user_id' => $this->approvedStudent->id,
            'completed' => false,
        ]);

        StudyTask::factory()->count(2)->create([
            'user_id' => $this->approvedStudent->id,
            'completed' => true,
            'completed_at' => now(),
        ]);

        $response = $this->actingAs($this->approvedStudent)
            ->getJson('/api/v1/learning/progress');

        $response->assertOk()
            ->assertJsonPath('data.active_tasks', 4)
            ->assertJsonPath('data.completed_tasks', 2);
    }

    public function test_progress_summary_counts_bookmarks()
    {
        for ($i = 0; $i < 7; $i++) {
            $material = CourseMaterial::factory()->create([
                'course_offering_id' => $this->material->course_offering_id,
                'status' => 'published',
            ]);
            Bookmark::factory()->create([
                'user_id' => $this->approvedStudent->id,
                'bookmarkable_type' => CourseMaterial::class,
                'bookmarkable_id' => $material->id,
            ]);
        }

        $response = $this->actingAs($this->approvedStudent)
            ->getJson('/api/v1/learning/progress');

        $response->assertOk()
            ->assertJsonPath('data.bookmarks', 7);
    }

    public function test_progress_summary_only_includes_users_own_data()
    {
        // Approved student's data
        for ($i = 0; $i < 2; $i++) {
            $material = CourseMaterial::factory()->create([
                'course_offering_id' => $this->material->course_offering_id,
                'status' => 'published',
            ]);
            GeneratedNote::factory()->create([
                'course_material_id' => $material->id,
                'generated_by' => $this->approvedStudent->id,
            ]);
        }

        StudyTask::factory()->count(3)->create([
            'user_id' => $this->approvedStudent->id,
            'completed' => false,
        ]);

        // Other student's data
        for ($i = 0; $i < 10; $i++) {
            $material = CourseMaterial::factory()->create([
                'course_offering_id' => $this->material->course_offering_id,
                'status' => 'published',
            ]);
            GeneratedNote::factory()->create([
                'course_material_id' => $material->id,
                'generated_by' => $this->otherStudent->id,
            ]);
        }

        StudyTask::factory()->count(10)->create([
            'user_id' => $this->otherStudent->id,
            'completed' => false,
        ]);

        $response = $this->actingAs($this->approvedStudent)
            ->getJson('/api/v1/learning/progress');

        $response->assertOk()
            ->assertJsonPath('data.notes_generated', 2)
            ->assertJsonPath('data.active_tasks', 3);
    }

    public function test_progress_summary_has_all_metrics()
    {
        GeneratedNote::factory()->create([
            'course_material_id' => $this->material->id,
            'generated_by' => $this->approvedStudent->id,
        ]);

        GeneratedQuiz::factory()->create([
            'course_material_id' => $this->material->id,
            'generated_by' => $this->approvedStudent->id,
        ]);

        StudyTask::factory()->create([
            'user_id' => $this->approvedStudent->id,
            'completed' => false,
        ]);

        StudyTask::factory()->create([
            'user_id' => $this->approvedStudent->id,
            'completed' => true,
            'completed_at' => now(),
        ]);

        Bookmark::factory()->create([
            'user_id' => $this->approvedStudent->id,
            'bookmarkable_type' => CourseMaterial::class,
            'bookmarkable_id' => $this->material->id,
        ]);

        $response = $this->actingAs($this->approvedStudent)
            ->getJson('/api/v1/learning/progress');

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'notes_generated' => 1,
                    'quizzes_generated' => 1,
                    'active_tasks' => 1,
                    'completed_tasks' => 1,
                    'bookmarks' => 1,
                ],
            ]);
    }
}
