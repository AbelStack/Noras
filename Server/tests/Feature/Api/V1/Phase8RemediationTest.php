<?php

namespace Tests\Feature\Api\V1;

use App\Models\Bookmark;
use App\Models\CourseMaterial;
use App\Models\CourseOffering;
use App\Models\GeneratedNote;
use App\Models\GeneratedQuiz;
use App\Models\StudyTask;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase8RemediationTest extends TestCase
{
    use RefreshDatabase;

    private User $student;
    private User $otherStudent;
    private User $admin;
    private string $token;
    private string $otherToken;
    private string $adminToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->student = User::factory()->create([
            'role' => 'student',
            'account_status' => 'approved',
        ]);
        $this->otherStudent = User::factory()->create([
            'role' => 'student',
            'account_status' => 'approved',
        ]);
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'account_status' => 'approved',
        ]);

        $this->token = $this->student->createToken('test')->plainTextToken;
        $this->otherToken = $this->otherStudent->createToken('test')->plainTextToken;
        $this->adminToken = $this->admin->createToken('test')->plainTextToken;
    }

    // ===================================================================
    // Priority 2: Study task foreign keys and deletion behavior
    // ===================================================================

    public function test_deleting_material_preserves_study_tasks_and_clears_reference(): void
    {
        $material = CourseMaterial::factory()->published()->create();

        $task = new StudyTask([
            'title' => 'Review material',
            'course_material_id' => $material->id,
        ]);
        $task->user_id = $this->student->id;
        $task->save();

        $this->assertNotNull($task->fresh()->course_material_id);

        // Admin deletes the material
        $response = $this->withToken($this->adminToken)
            ->deleteJson("/api/v1/admin/materials/{$material->id}");

        $response->assertOk();

        // Task still exists but reference is cleared
        $freshTask = $task->fresh();
        $this->assertNotNull($freshTask, 'Task should be preserved after material deletion');
        $this->assertNull($freshTask->course_material_id, 'Material reference should be nullified');
    }

    public function test_deleting_note_preserves_study_tasks_and_clears_reference(): void
    {
        $material = CourseMaterial::factory()->published()->create();
        $note = GeneratedNote::factory()->create([
            'course_material_id' => $material->id,
            'generated_by' => $this->student->id,
        ]);

        $task = new StudyTask([
            'title' => 'Study notes',
            'generated_note_id' => $note->id,
        ]);
        $task->user_id = $this->student->id;
        $task->save();

        // Student deletes their note
        $response = $this->withToken($this->token)
            ->deleteJson("/api/v1/notes/{$note->id}");

        $response->assertOk();

        $freshTask = $task->fresh();
        $this->assertNotNull($freshTask, 'Task should be preserved after note deletion');
        $this->assertNull($freshTask->generated_note_id, 'Note reference should be nullified');
    }

    public function test_deleting_quiz_preserves_study_tasks_and_clears_reference(): void
    {
        $material = CourseMaterial::factory()->published()->create();
        $quiz = GeneratedQuiz::factory()->create([
            'course_material_id' => $material->id,
            'generated_by' => $this->student->id,
        ]);

        $task = new StudyTask([
            'title' => 'Take quiz',
            'generated_quiz_id' => $quiz->id,
        ]);
        $task->user_id = $this->student->id;
        $task->save();

        $response = $this->withToken($this->token)
            ->deleteJson("/api/v1/quizzes/{$quiz->id}");

        $response->assertOk();

        $freshTask = $task->fresh();
        $this->assertNotNull($freshTask, 'Task should be preserved after quiz deletion');
        $this->assertNull($freshTask->generated_quiz_id, 'Quiz reference should be nullified');
    }

    public function test_material_deletion_returns_json_error_not_exception(): void
    {
        // Non-existent material
        $response = $this->withToken($this->adminToken)
            ->deleteJson('/api/v1/admin/materials/99999');

        $response->assertStatus(404);
        $response->assertJsonStructure(['message']);
    }

    // ===================================================================
    // Priority 3: Orphaned bookmarks
    // ===================================================================

    public function test_deleting_material_cleans_up_bookmarks(): void
    {
        $material = CourseMaterial::factory()->published()->create();

        $bookmark = new Bookmark([
            'bookmarkable_type' => CourseMaterial::class,
            'bookmarkable_id' => $material->id,
        ]);
        $bookmark->user_id = $this->student->id;
        $bookmark->save();

        $this->assertDatabaseHas('bookmarks', ['id' => $bookmark->id]);

        $this->withToken($this->adminToken)
            ->deleteJson("/api/v1/admin/materials/{$material->id}");

        $this->assertDatabaseMissing('bookmarks', ['id' => $bookmark->id]);
    }

    public function test_deleting_note_cleans_up_bookmarks(): void
    {
        $material = CourseMaterial::factory()->published()->create();
        $note = GeneratedNote::factory()->create([
            'course_material_id' => $material->id,
            'generated_by' => $this->student->id,
        ]);

        $bookmark = new Bookmark([
            'bookmarkable_type' => GeneratedNote::class,
            'bookmarkable_id' => $note->id,
        ]);
        $bookmark->user_id = $this->student->id;
        $bookmark->save();

        $this->withToken($this->token)
            ->deleteJson("/api/v1/notes/{$note->id}");

        $this->assertDatabaseMissing('bookmarks', ['id' => $bookmark->id]);
    }

    public function test_deleting_quiz_cleans_up_bookmarks(): void
    {
        $material = CourseMaterial::factory()->published()->create();
        $quiz = GeneratedQuiz::factory()->create([
            'course_material_id' => $material->id,
            'generated_by' => $this->student->id,
        ]);

        $bookmark = new Bookmark([
            'bookmarkable_type' => GeneratedQuiz::class,
            'bookmarkable_id' => $quiz->id,
        ]);
        $bookmark->user_id = $this->student->id;
        $bookmark->save();

        $this->withToken($this->token)
            ->deleteJson("/api/v1/quizzes/{$quiz->id}");

        $this->assertDatabaseMissing('bookmarks', ['id' => $bookmark->id]);
    }

    public function test_bookmark_listing_excludes_orphaned_bookmarks(): void
    {
        $material = CourseMaterial::factory()->published()->create();

        // Create a valid bookmark
        $validBookmark = new Bookmark([
            'bookmarkable_type' => CourseMaterial::class,
            'bookmarkable_id' => $material->id,
        ]);
        $validBookmark->user_id = $this->student->id;
        $validBookmark->save();

        // Create an orphaned bookmark (referencing non-existent resource)
        $orphanedBookmark = new Bookmark([
            'bookmarkable_type' => CourseMaterial::class,
            'bookmarkable_id' => 99999,
        ]);
        $orphanedBookmark->user_id = $this->student->id;
        $orphanedBookmark->save();

        $response = $this->withToken($this->token)
            ->getJson('/api/v1/bookmarks');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    }

    public function test_showing_orphaned_bookmark_auto_cleans_and_returns_404(): void
    {
        $orphanedBookmark = new Bookmark([
            'bookmarkable_type' => CourseMaterial::class,
            'bookmarkable_id' => 99999,
        ]);
        $orphanedBookmark->user_id = $this->student->id;
        $orphanedBookmark->save();

        $response = $this->withToken($this->token)
            ->getJson("/api/v1/bookmarks/{$orphanedBookmark->id}");

        $response->assertStatus(404);
        $this->assertDatabaseMissing('bookmarks', ['id' => $orphanedBookmark->id]);
    }

    public function test_repeated_bookmark_deletion_returns_404(): void
    {
        $material = CourseMaterial::factory()->published()->create();

        $bookmark = new Bookmark([
            'bookmarkable_type' => CourseMaterial::class,
            'bookmarkable_id' => $material->id,
        ]);
        $bookmark->user_id = $this->student->id;
        $bookmark->save();

        $first = $this->withToken($this->token)
            ->deleteJson("/api/v1/bookmarks/{$bookmark->id}");
        $first->assertOk();

        $second = $this->withToken($this->token)
            ->deleteJson("/api/v1/bookmarks/{$bookmark->id}");
        $second->assertStatus(404);
    }

    // ===================================================================
    // Priority 4: Learning history pagination and sorting
    // ===================================================================

    public function test_learning_history_returns_newest_first(): void
    {
        $material1 = CourseMaterial::factory()->published()->create();
        $material2 = CourseMaterial::factory()->published()->create();
        $material3 = CourseMaterial::factory()->published()->create();

        $olderNote = GeneratedNote::factory()->create([
            'course_material_id' => $material1->id,
            'generated_by' => $this->student->id,
            'created_at' => now()->subDays(2),
        ]);
        $newerQuiz = GeneratedQuiz::factory()->create([
            'course_material_id' => $material2->id,
            'generated_by' => $this->student->id,
            'created_at' => now()->subDay(),
        ]);
        $newestNote = GeneratedNote::factory()->create([
            'course_material_id' => $material3->id,
            'generated_by' => $this->student->id,
            'created_at' => now(),
        ]);

        $response = $this->withToken($this->token)
            ->getJson('/api/v1/learning/history');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(3, $data);
        $this->assertEquals($newestNote->id, $data[0]['id']);
        $this->assertEquals('note', $data[0]['type']);
    }

    public function test_learning_history_pagination_works(): void
    {
        // Create 5 distinct notes with distinct materials
        for ($i = 0; $i < 5; $i++) {
            $mat = CourseMaterial::factory()->published()->create();
            GeneratedNote::factory()->create([
                'course_material_id' => $mat->id,
                'generated_by' => $this->student->id,
                'created_at' => now()->subMinutes($i),
            ]);
        }

        // Page 1 with 3 per page
        $response = $this->withToken($this->token)
            ->getJson('/api/v1/learning/history?per_page=3&page=1');

        $response->assertOk();
        $this->assertCount(3, $response->json('data'));
        $this->assertEquals(5, $response->json('pagination.total'));
        $this->assertEquals(2, $response->json('pagination.last_page'));

        // Page 2
        $response2 = $this->withToken($this->token)
            ->getJson('/api/v1/learning/history?per_page=3&page=2');

        $response2->assertOk();
        $this->assertCount(2, $response2->json('data'));
    }

    public function test_learning_history_enforces_ownership(): void
    {
        $material = CourseMaterial::factory()->published()->create();

        // Create notes for different students
        GeneratedNote::factory()->create([
            'course_material_id' => $material->id,
            'generated_by' => $this->student->id,
        ]);
        GeneratedNote::factory()->create([
            'course_material_id' => $material->id,
            'generated_by' => $this->otherStudent->id,
        ]);

        $response = $this->withToken($this->token)
            ->getJson('/api/v1/learning/history');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_learning_history_empty_returns_valid_response(): void
    {
        $response = $this->withToken($this->token)
            ->getJson('/api/v1/learning/history');

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'data' => [],
            'pagination' => [
                'current_page' => 1,
                'total' => 0,
            ],
        ]);
    }

    public function test_learning_history_mixes_notes_and_quizzes(): void
    {
        $material = CourseMaterial::factory()->published()->create();

        GeneratedNote::factory()->create([
            'course_material_id' => $material->id,
            'generated_by' => $this->student->id,
        ]);
        GeneratedQuiz::factory()->create([
            'course_material_id' => $material->id,
            'generated_by' => $this->student->id,
        ]);

        $response = $this->withToken($this->token)
            ->getJson('/api/v1/learning/history');

        $response->assertOk();
        $types = collect($response->json('data'))->pluck('type')->toArray();
        $this->assertContains('note', $types);
        $this->assertContains('quiz', $types);
    }

    public function test_learning_progress_returns_correct_counts(): void
    {
        $material = CourseMaterial::factory()->published()->create();

        GeneratedNote::factory()->create([
            'course_material_id' => $material->id,
            'generated_by' => $this->student->id,
        ]);
        GeneratedQuiz::factory()->create([
            'course_material_id' => $material->id,
            'generated_by' => $this->student->id,
        ]);

        $activeTask = new StudyTask(['title' => 'Active']);
        $activeTask->user_id = $this->student->id;
        $activeTask->save();

        $completedTask = new StudyTask(['title' => 'Done']);
        $completedTask->user_id = $this->student->id;
        $completedTask->completed = true;
        $completedTask->completed_at = now();
        $completedTask->save();

        $bookmark = new Bookmark([
            'bookmarkable_type' => CourseMaterial::class,
            'bookmarkable_id' => $material->id,
        ]);
        $bookmark->user_id = $this->student->id;
        $bookmark->save();

        $response = $this->withToken($this->token)
            ->getJson('/api/v1/learning/progress');

        $response->assertOk();
        $response->assertJson([
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

    // ===================================================================
    // Priority 5: Mass assignment protection
    // ===================================================================

    public function test_study_task_cannot_mass_assign_user_id(): void
    {
        $material = CourseMaterial::factory()->published()->create();

        $response = $this->withToken($this->token)
            ->postJson('/api/v1/tasks', [
                'title' => 'Test task',
                'user_id' => $this->otherStudent->id, // Attempting to assign to another user
            ]);

        $response->assertStatus(201);

        // Task should belong to authenticated user, not the spoofed user_id
        $task = StudyTask::latest()->first();
        $this->assertEquals($this->student->id, $task->user_id);
    }

    public function test_study_task_cannot_mass_assign_completed(): void
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/v1/tasks', [
                'title' => 'Test task',
                'completed' => true,
                'completed_at' => now()->toISOString(),
            ]);

        $response->assertStatus(201);

        $task = StudyTask::latest()->first();
        $this->assertFalse($task->completed);
        $this->assertNull($task->completed_at);
    }

    public function test_study_task_complete_uses_dedicated_endpoint(): void
    {
        $task = new StudyTask(['title' => 'Test task']);
        $task->user_id = $this->student->id;
        $task->save();

        $response = $this->withToken($this->token)
            ->postJson("/api/v1/tasks/{$task->id}/complete");

        $response->assertOk();

        $freshTask = $task->fresh();
        $this->assertTrue($freshTask->completed);
        $this->assertNotNull($freshTask->completed_at);
    }

    public function test_study_task_reopen_uses_dedicated_endpoint(): void
    {
        $task = new StudyTask(['title' => 'Done task']);
        $task->user_id = $this->student->id;
        $task->completed = true;
        $task->completed_at = now();
        $task->save();

        $response = $this->withToken($this->token)
            ->postJson("/api/v1/tasks/{$task->id}/reopen");

        $response->assertOk();

        $freshTask = $task->fresh();
        $this->assertFalse($freshTask->completed);
        $this->assertNull($freshTask->completed_at);
    }

    // ===================================================================
    // Cross-student authorization
    // ===================================================================

    public function test_cannot_view_other_students_tasks(): void
    {
        $task = new StudyTask(['title' => 'Private task']);
        $task->user_id = $this->student->id;
        $task->save();

        $response = $this->withToken($this->otherToken)
            ->getJson("/api/v1/tasks/{$task->id}");

        $response->assertStatus(403);
    }

    public function test_cannot_delete_other_students_tasks(): void
    {
        $task = new StudyTask(['title' => 'Private task']);
        $task->user_id = $this->student->id;
        $task->save();

        $response = $this->withToken($this->otherToken)
            ->deleteJson("/api/v1/tasks/{$task->id}");

        $response->assertStatus(403);
    }

    public function test_cannot_complete_other_students_tasks(): void
    {
        $task = new StudyTask(['title' => 'Private task']);
        $task->user_id = $this->student->id;
        $task->save();

        $response = $this->withToken($this->otherToken)
            ->postJson("/api/v1/tasks/{$task->id}/complete");

        $response->assertStatus(403);
    }

    public function test_cannot_view_other_students_bookmarks(): void
    {
        $material = CourseMaterial::factory()->published()->create();

        $bookmark = new Bookmark([
            'bookmarkable_type' => CourseMaterial::class,
            'bookmarkable_id' => $material->id,
        ]);
        $bookmark->user_id = $this->student->id;
        $bookmark->save();

        $response = $this->withToken($this->otherToken)
            ->getJson("/api/v1/bookmarks/{$bookmark->id}");

        $response->assertStatus(403);
    }

    public function test_cannot_delete_other_students_bookmarks(): void
    {
        $material = CourseMaterial::factory()->published()->create();

        $bookmark = new Bookmark([
            'bookmarkable_type' => CourseMaterial::class,
            'bookmarkable_id' => $material->id,
        ]);
        $bookmark->user_id = $this->student->id;
        $bookmark->save();

        $response = $this->withToken($this->otherToken)
            ->deleteJson("/api/v1/bookmarks/{$bookmark->id}");

        $response->assertStatus(403);
    }

    public function test_cannot_link_other_students_note_to_task(): void
    {
        $material = CourseMaterial::factory()->published()->create();
        $othersNote = GeneratedNote::factory()->create([
            'course_material_id' => $material->id,
            'generated_by' => $this->otherStudent->id,
        ]);

        $response = $this->withToken($this->token)
            ->postJson('/api/v1/tasks', [
                'title' => 'Study notes',
                'generated_note_id' => $othersNote->id,
            ]);

        $response->assertStatus(403);
    }

    public function test_cannot_bookmark_other_students_note(): void
    {
        $material = CourseMaterial::factory()->published()->create();
        $othersNote = GeneratedNote::factory()->create([
            'course_material_id' => $material->id,
            'generated_by' => $this->otherStudent->id,
        ]);

        $response = $this->withToken($this->token)
            ->postJson('/api/v1/bookmarks', [
                'bookmarkable_type' => GeneratedNote::class,
                'bookmarkable_id' => $othersNote->id,
            ]);

        $response->assertStatus(403);
    }

    // ===================================================================
    // Null/missing linked resources
    // ===================================================================

    public function test_task_creation_with_nonexistent_note_returns_error(): void
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/v1/tasks', [
                'title' => 'Study',
                'generated_note_id' => 99999,
            ]);

        // Should return 422 (validation) or 404 (not found)
        $this->assertTrue(in_array($response->status(), [404, 422]));
    }

    public function test_task_creation_with_nonexistent_quiz_returns_error(): void
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/v1/tasks', [
                'title' => 'Study',
                'generated_quiz_id' => 99999,
            ]);

        $this->assertTrue(in_array($response->status(), [404, 422]));
    }

    public function test_task_creation_with_unpublished_material_returns_error(): void
    {
        $material = CourseMaterial::factory()->create(['status' => 'draft']);

        $response = $this->withToken($this->token)
            ->postJson('/api/v1/tasks', [
                'title' => 'Study',
                'course_material_id' => $material->id,
            ]);

        // Should return 422 (validation - exists rule on published) or 403
        $this->assertTrue(in_array($response->status(), [403, 404, 422]));
    }

    // ===================================================================
    // Bookmark CRUD
    // ===================================================================

    public function test_bookmark_create_success(): void
    {
        $material = CourseMaterial::factory()->published()->create();

        $response = $this->withToken($this->token)
            ->postJson('/api/v1/bookmarks', [
                'bookmarkable_type' => CourseMaterial::class,
                'bookmarkable_id' => $material->id,
            ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
    }

    public function test_bookmark_duplicate_prevention(): void
    {
        $material = CourseMaterial::factory()->published()->create();

        $this->withToken($this->token)
            ->postJson('/api/v1/bookmarks', [
                'bookmarkable_type' => CourseMaterial::class,
                'bookmarkable_id' => $material->id,
            ])->assertStatus(201);

        $this->withToken($this->token)
            ->postJson('/api/v1/bookmarks', [
                'bookmarkable_type' => CourseMaterial::class,
                'bookmarkable_id' => $material->id,
            ])->assertStatus(409);
    }

    public function test_bookmark_unpublished_material_rejected(): void
    {
        $material = CourseMaterial::factory()->create(['status' => 'draft']);

        $response = $this->withToken($this->token)
            ->postJson('/api/v1/bookmarks', [
                'bookmarkable_type' => CourseMaterial::class,
                'bookmarkable_id' => $material->id,
            ]);

        $response->assertStatus(403);
    }

    // ===================================================================
    // Study Task CRUD
    // ===================================================================

    public function test_study_task_crud_lifecycle(): void
    {
        // Create
        $response = $this->withToken($this->token)
            ->postJson('/api/v1/tasks', [
                'title' => 'My Task',
                'description' => 'Test description',
            ]);
        $response->assertStatus(201);
        $taskId = $response->json('data.id');

        // Read
        $this->withToken($this->token)
            ->getJson("/api/v1/tasks/{$taskId}")
            ->assertOk()
            ->assertJsonPath('data.title', 'My Task');

        // Update
        $this->withToken($this->token)
            ->putJson("/api/v1/tasks/{$taskId}", ['title' => 'Updated Task'])
            ->assertOk()
            ->assertJsonPath('data.title', 'Updated Task');

        // Delete
        $this->withToken($this->token)
            ->deleteJson("/api/v1/tasks/{$taskId}")
            ->assertOk();

        $this->assertDatabaseMissing('study_tasks', ['id' => $taskId]);
    }

    public function test_study_task_listing_pagination(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $task = new StudyTask(['title' => "Task {$i}"]);
            $task->user_id = $this->student->id;
            $task->save();
        }

        $response = $this->withToken($this->token)
            ->getJson('/api/v1/tasks?per_page=2');

        $response->assertOk();
        $this->assertCount(2, $response->json('data'));
        $this->assertEquals(5, $response->json('pagination.total'));
    }

    public function test_study_task_filter_by_completed(): void
    {
        $active = new StudyTask(['title' => 'Active']);
        $active->user_id = $this->student->id;
        $active->save();

        $done = new StudyTask(['title' => 'Done']);
        $done->user_id = $this->student->id;
        $done->completed = true;
        $done->completed_at = now();
        $done->save();

        $response = $this->withToken($this->token)
            ->getJson('/api/v1/tasks?completed=true');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    // ===================================================================
    // Authentication requirements
    // ===================================================================

    public function test_unauthenticated_access_rejected(): void
    {
        $this->getJson('/api/v1/tasks')->assertStatus(401);
        $this->getJson('/api/v1/bookmarks')->assertStatus(401);
        $this->getJson('/api/v1/learning/history')->assertStatus(401);
        $this->getJson('/api/v1/learning/progress')->assertStatus(401);
    }

    public function test_unapproved_student_rejected(): void
    {
        $pending = User::factory()->create([
            'role' => 'student',
            'account_status' => 'pending',
        ]);
        $pendingToken = $pending->createToken('test')->plainTextToken;

        $this->withToken($pendingToken)
            ->getJson('/api/v1/tasks')
            ->assertStatus(403);

        $this->withToken($pendingToken)
            ->getJson('/api/v1/bookmarks')
            ->assertStatus(403);
    }
}
