<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\StudyTask;
use App\Models\CourseMaterial;
use App\Models\GeneratedNote;
use App\Models\GeneratedQuiz;
use App\Models\University;
use App\Models\Department;
use App\Models\Course;
use App\Models\CourseOffering;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudyTaskControllerTest extends TestCase
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

    public function test_unauthenticated_user_cannot_access_tasks()
    {
        $this->getJson('/api/v1/tasks')->assertUnauthorized();
        $this->postJson('/api/v1/tasks')->assertUnauthorized();
    }

    public function test_approved_student_can_create_basic_task()
    {
        $response = $this->actingAs($this->approvedStudent)
            ->postJson('/api/v1/tasks', [
                'title' => 'Review Chapter 3',
                'description' => 'Complete all exercises',
                'due_date' => now()->addDays(7)->format('Y-m-d'),
            ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['id', 'user_id', 'title', 'description', 'completed', 'due_date', 'created_at'],
            ]);

        $this->assertDatabaseHas('study_tasks', [
            'user_id' => $this->approvedStudent->id,
            'title' => 'Review Chapter 3',
            'completed' => false,
        ]);
    }

    public function test_student_can_create_task_linked_to_material()
    {
        $response = $this->actingAs($this->approvedStudent)
            ->postJson('/api/v1/tasks', [
                'title' => 'Study Material',
                'course_material_id' => $this->material->id,
            ]);

        $response->assertCreated();
        $this->assertDatabaseHas('study_tasks', [
            'user_id' => $this->approvedStudent->id,
            'course_material_id' => $this->material->id,
        ]);
    }

    public function test_student_can_create_task_linked_to_their_note()
    {
        $note = GeneratedNote::factory()->create([
            'course_material_id' => $this->material->id,
            'generated_by' => $this->approvedStudent->id,
        ]);

        $response = $this->actingAs($this->approvedStudent)
            ->postJson('/api/v1/tasks', [
                'title' => 'Review Notes',
                'generated_note_id' => $note->id,
            ]);

        $response->assertCreated();
        $this->assertDatabaseHas('study_tasks', [
            'user_id' => $this->approvedStudent->id,
            'generated_note_id' => $note->id,
        ]);
    }

    public function test_student_cannot_link_task_to_another_students_note()
    {
        $note = GeneratedNote::factory()->create([
            'course_material_id' => $this->material->id,
            'generated_by' => $this->otherStudent->id,
        ]);

        $response = $this->actingAs($this->approvedStudent)
            ->postJson('/api/v1/tasks', [
                'title' => 'Review Notes',
                'generated_note_id' => $note->id,
            ]);

        $response->assertForbidden()
            ->assertJson([
                'success' => false,
                'message' => 'You can only link your own notes to tasks.',
            ]);
    }

    public function test_student_can_create_task_linked_to_their_quiz()
    {
        $quiz = GeneratedQuiz::factory()->create([
            'course_material_id' => $this->material->id,
            'generated_by' => $this->approvedStudent->id,
        ]);

        $response = $this->actingAs($this->approvedStudent)
            ->postJson('/api/v1/tasks', [
                'title' => 'Take Quiz',
                'generated_quiz_id' => $quiz->id,
            ]);

        $response->assertCreated();
        $this->assertDatabaseHas('study_tasks', [
            'user_id' => $this->approvedStudent->id,
            'generated_quiz_id' => $quiz->id,
        ]);
    }

    public function test_student_cannot_link_task_to_another_students_quiz()
    {
        $quiz = GeneratedQuiz::factory()->create([
            'course_material_id' => $this->material->id,
            'generated_by' => $this->otherStudent->id,
        ]);

        $response = $this->actingAs($this->approvedStudent)
            ->postJson('/api/v1/tasks', [
                'title' => 'Take Quiz',
                'generated_quiz_id' => $quiz->id,
            ]);

        $response->assertForbidden()
            ->assertJson([
                'success' => false,
                'message' => 'You can only link your own quizzes to tasks.',
            ]);
    }

    public function test_student_cannot_link_task_to_draft_material()
    {
        $draftMaterial = CourseMaterial::factory()->create([
            'course_offering_id' => $this->material->course_offering_id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->approvedStudent)
            ->postJson('/api/v1/tasks', [
                'title' => 'Study Draft',
                'course_material_id' => $draftMaterial->id,
            ]);

        $response->assertForbidden()
            ->assertJson([
                'success' => false,
                'message' => 'You can only link published materials to tasks.',
            ]);
    }

    public function test_validation_fails_for_missing_title()
    {
        $response = $this->actingAs($this->approvedStudent)
            ->postJson('/api/v1/tasks', [
                'description' => 'No title provided',
            ]);

        $response->assertUnprocessable();
    }

    public function test_validation_fails_for_past_due_date()
    {
        $response = $this->actingAs($this->approvedStudent)
            ->postJson('/api/v1/tasks', [
                'title' => 'Past Due Task',
                'due_date' => now()->subDays(1)->format('Y-m-d'),
            ]);

        $response->assertUnprocessable();
    }

    public function test_student_can_list_their_tasks()
    {
        StudyTask::factory()->count(3)->create([
            'user_id' => $this->approvedStudent->id,
        ]);

        StudyTask::factory()->create([
            'user_id' => $this->otherStudent->id,
        ]);

        $response = $this->actingAs($this->approvedStudent)
            ->getJson('/api/v1/tasks');

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'user_id', 'title', 'completed', 'due_date'],
                ],
                'pagination',
            ]);
    }

    public function test_student_can_filter_tasks_by_completion_status()
    {
        StudyTask::factory()->create([
            'user_id' => $this->approvedStudent->id,
            'completed' => false,
        ]);

        StudyTask::factory()->count(2)->create([
            'user_id' => $this->approvedStudent->id,
            'completed' => true,
            'completed_at' => now(),
        ]);

        $response = $this->actingAs($this->approvedStudent)
            ->getJson('/api/v1/tasks?completed=false');

        $response->assertOk()
            ->assertJsonCount(1, 'data');

        $response = $this->actingAs($this->approvedStudent)
            ->getJson('/api/v1/tasks?completed=true');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_student_can_view_their_own_task()
    {
        $task = StudyTask::factory()->create([
            'user_id' => $this->approvedStudent->id,
        ]);

        $response = $this->actingAs($this->approvedStudent)
            ->getJson("/api/v1/tasks/{$task->id}");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $task->id,
                    'user_id' => $this->approvedStudent->id,
                ],
            ]);
    }

    public function test_student_cannot_view_another_students_task()
    {
        $task = StudyTask::factory()->create([
            'user_id' => $this->otherStudent->id,
        ]);

        $response = $this->actingAs($this->approvedStudent)
            ->getJson("/api/v1/tasks/{$task->id}");

        $response->assertForbidden();
    }

    public function test_student_can_update_their_own_task()
    {
        $task = StudyTask::factory()->create([
            'user_id' => $this->approvedStudent->id,
            'title' => 'Original Title',
        ]);

        $response = $this->actingAs($this->approvedStudent)
            ->putJson("/api/v1/tasks/{$task->id}", [
                'title' => 'Updated Title',
                'description' => 'New description',
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'title' => 'Updated Title',
                    'description' => 'New description',
                ],
            ]);

        $this->assertDatabaseHas('study_tasks', [
            'id' => $task->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_student_cannot_update_another_students_task()
    {
        $task = StudyTask::factory()->create([
            'user_id' => $this->otherStudent->id,
        ]);

        $response = $this->actingAs($this->approvedStudent)
            ->putJson("/api/v1/tasks/{$task->id}", [
                'title' => 'Hacked Title',
            ]);

        $response->assertForbidden();
    }

    public function test_student_can_delete_their_own_task()
    {
        $task = StudyTask::factory()->create([
            'user_id' => $this->approvedStudent->id,
        ]);

        $response = $this->actingAs($this->approvedStudent)
            ->deleteJson("/api/v1/tasks/{$task->id}");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Study task deleted successfully.',
            ]);

        $this->assertDatabaseMissing('study_tasks', ['id' => $task->id]);
    }

    public function test_student_cannot_delete_another_students_task()
    {
        $task = StudyTask::factory()->create([
            'user_id' => $this->otherStudent->id,
        ]);

        $response = $this->actingAs($this->approvedStudent)
            ->deleteJson("/api/v1/tasks/{$task->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('study_tasks', ['id' => $task->id]);
    }

    public function test_student_can_mark_task_as_completed()
    {
        $task = StudyTask::factory()->create([
            'user_id' => $this->approvedStudent->id,
            'completed' => false,
        ]);

        $response = $this->actingAs($this->approvedStudent)
            ->postJson("/api/v1/tasks/{$task->id}/complete");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Task marked as completed.',
                'data' => [
                    'completed' => true,
                ],
            ]);

        $task->refresh();
        $this->assertTrue($task->completed);
        $this->assertNotNull($task->completed_at);
    }

    public function test_cannot_complete_already_completed_task()
    {
        $task = StudyTask::factory()->create([
            'user_id' => $this->approvedStudent->id,
            'completed' => true,
            'completed_at' => now(),
        ]);

        $response = $this->actingAs($this->approvedStudent)
            ->postJson("/api/v1/tasks/{$task->id}/complete");

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Task is already completed.',
            ]);
    }

    public function test_student_cannot_complete_another_students_task()
    {
        $task = StudyTask::factory()->create([
            'user_id' => $this->otherStudent->id,
            'completed' => false,
        ]);

        $response = $this->actingAs($this->approvedStudent)
            ->postJson("/api/v1/tasks/{$task->id}/complete");

        $response->assertForbidden();
    }

    public function test_student_can_reopen_completed_task()
    {
        $task = StudyTask::factory()->create([
            'user_id' => $this->approvedStudent->id,
            'completed' => true,
            'completed_at' => now(),
        ]);

        $response = $this->actingAs($this->approvedStudent)
            ->postJson("/api/v1/tasks/{$task->id}/reopen");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Task reopened.',
                'data' => [
                    'completed' => false,
                ],
            ]);

        $task->refresh();
        $this->assertFalse($task->completed);
        $this->assertNull($task->completed_at);
    }

    public function test_cannot_reopen_non_completed_task()
    {
        $task = StudyTask::factory()->create([
            'user_id' => $this->approvedStudent->id,
            'completed' => false,
        ]);

        $response = $this->actingAs($this->approvedStudent)
            ->postJson("/api/v1/tasks/{$task->id}/reopen");

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Task is not completed.',
            ]);
    }

    public function test_student_cannot_reopen_another_students_task()
    {
        $task = StudyTask::factory()->create([
            'user_id' => $this->otherStudent->id,
            'completed' => true,
            'completed_at' => now(),
        ]);

        $response = $this->actingAs($this->approvedStudent)
            ->postJson("/api/v1/tasks/{$task->id}/reopen");

        $response->assertForbidden();
    }

    public function test_tasks_respect_pagination()
    {
        StudyTask::factory()->count(25)->create([
            'user_id' => $this->approvedStudent->id,
        ]);

        $response = $this->actingAs($this->approvedStudent)
            ->getJson('/api/v1/tasks?per_page=10');

        $response->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('pagination.per_page', 10)
            ->assertJsonPath('pagination.total', 25);
    }
}
