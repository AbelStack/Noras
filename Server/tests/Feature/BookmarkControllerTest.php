<?php

namespace Tests\Feature;

use App\Models\User;
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

class BookmarkControllerTest extends TestCase
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

    public function test_unauthenticated_user_cannot_access_bookmarks()
    {
        $this->getJson('/api/v1/bookmarks')->assertUnauthorized();
        $this->postJson('/api/v1/bookmarks')->assertUnauthorized();
    }

    public function test_approved_student_can_create_bookmark_for_published_material()
    {
        $response = $this->actingAs($this->approvedStudent)
            ->postJson('/api/v1/bookmarks', [
                'bookmarkable_type' => CourseMaterial::class,
                'bookmarkable_id' => $this->material->id,
            ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['id', 'user_id', 'bookmarkable_type', 'bookmarkable_id', 'created_at'],
            ]);

        $this->assertDatabaseHas('bookmarks', [
            'user_id' => $this->approvedStudent->id,
            'bookmarkable_type' => CourseMaterial::class,
            'bookmarkable_id' => $this->material->id,
        ]);
    }

    public function test_student_cannot_bookmark_draft_material()
    {
        $draftMaterial = CourseMaterial::factory()->create([
            'course_offering_id' => $this->material->course_offering_id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->approvedStudent)
            ->postJson('/api/v1/bookmarks', [
                'bookmarkable_type' => CourseMaterial::class,
                'bookmarkable_id' => $draftMaterial->id,
            ]);

        $response->assertForbidden()
            ->assertJson([
                'success' => false,
                'message' => 'You can only bookmark published materials.',
            ]);
    }

    public function test_student_can_bookmark_their_own_note()
    {
        $note = GeneratedNote::factory()->create([
            'course_material_id' => $this->material->id,
            'generated_by' => $this->approvedStudent->id,
        ]);

        $response = $this->actingAs($this->approvedStudent)
            ->postJson('/api/v1/bookmarks', [
                'bookmarkable_type' => GeneratedNote::class,
                'bookmarkable_id' => $note->id,
            ]);

        $response->assertCreated();
        $this->assertDatabaseHas('bookmarks', [
            'user_id' => $this->approvedStudent->id,
            'bookmarkable_type' => GeneratedNote::class,
            'bookmarkable_id' => $note->id,
        ]);
    }

    public function test_student_cannot_bookmark_another_students_note()
    {
        $note = GeneratedNote::factory()->create([
            'course_material_id' => $this->material->id,
            'generated_by' => $this->otherStudent->id,
        ]);

        $response = $this->actingAs($this->approvedStudent)
            ->postJson('/api/v1/bookmarks', [
                'bookmarkable_type' => GeneratedNote::class,
                'bookmarkable_id' => $note->id,
            ]);

        $response->assertForbidden()
            ->assertJson([
                'success' => false,
                'message' => 'You can only bookmark your own notes and quizzes.',
            ]);
    }

    public function test_student_can_bookmark_their_own_quiz()
    {
        $quiz = GeneratedQuiz::factory()->create([
            'course_material_id' => $this->material->id,
            'generated_by' => $this->approvedStudent->id,
        ]);

        $response = $this->actingAs($this->approvedStudent)
            ->postJson('/api/v1/bookmarks', [
                'bookmarkable_type' => GeneratedQuiz::class,
                'bookmarkable_id' => $quiz->id,
            ]);

        $response->assertCreated();
        $this->assertDatabaseHas('bookmarks', [
            'user_id' => $this->approvedStudent->id,
            'bookmarkable_type' => GeneratedQuiz::class,
            'bookmarkable_id' => $quiz->id,
        ]);
    }

    public function test_student_cannot_bookmark_another_students_quiz()
    {
        $quiz = GeneratedQuiz::factory()->create([
            'course_material_id' => $this->material->id,
            'generated_by' => $this->otherStudent->id,
        ]);

        $response = $this->actingAs($this->approvedStudent)
            ->postJson('/api/v1/bookmarks', [
                'bookmarkable_type' => GeneratedQuiz::class,
                'bookmarkable_id' => $quiz->id,
            ]);

        $response->assertForbidden();
    }

    public function test_cannot_create_duplicate_bookmark()
    {
        Bookmark::create([
            'user_id' => $this->approvedStudent->id,
            'bookmarkable_type' => CourseMaterial::class,
            'bookmarkable_id' => $this->material->id,
        ]);

        $response = $this->actingAs($this->approvedStudent)
            ->postJson('/api/v1/bookmarks', [
                'bookmarkable_type' => CourseMaterial::class,
                'bookmarkable_id' => $this->material->id,
            ]);

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'message' => 'Bookmark already exists.',
            ]);
    }

    public function test_cannot_bookmark_nonexistent_resource()
    {
        $response = $this->actingAs($this->approvedStudent)
            ->postJson('/api/v1/bookmarks', [
                'bookmarkable_type' => CourseMaterial::class,
                'bookmarkable_id' => 999999,
            ]);

        $response->assertNotFound()
            ->assertJson([
                'success' => false,
                'message' => 'Resource not found.',
            ]);
    }

    public function test_validation_fails_for_invalid_bookmarkable_type()
    {
        $response = $this->actingAs($this->approvedStudent)
            ->postJson('/api/v1/bookmarks', [
                'bookmarkable_type' => 'InvalidClass',
                'bookmarkable_id' => 1,
            ]);

        $response->assertUnprocessable();
    }

    public function test_student_can_list_their_bookmarks()
    {
        $bookmark1 = Bookmark::create([
            'user_id' => $this->approvedStudent->id,
            'bookmarkable_type' => CourseMaterial::class,
            'bookmarkable_id' => $this->material->id,
        ]);

        $note = GeneratedNote::factory()->create([
            'course_material_id' => $this->material->id,
            'generated_by' => $this->approvedStudent->id,
        ]);

        $bookmark2 = Bookmark::create([
            'user_id' => $this->approvedStudent->id,
            'bookmarkable_type' => GeneratedNote::class,
            'bookmarkable_id' => $note->id,
        ]);

        // Other student's bookmark
        Bookmark::create([
            'user_id' => $this->otherStudent->id,
            'bookmarkable_type' => CourseMaterial::class,
            'bookmarkable_id' => $this->material->id,
        ]);

        $response = $this->actingAs($this->approvedStudent)
            ->getJson('/api/v1/bookmarks');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'user_id', 'bookmarkable_type', 'bookmarkable_id'],
                ],
                'pagination',
            ]);
    }

    public function test_student_can_filter_bookmarks_by_type()
    {
        Bookmark::create([
            'user_id' => $this->approvedStudent->id,
            'bookmarkable_type' => CourseMaterial::class,
            'bookmarkable_id' => $this->material->id,
        ]);

        $note = GeneratedNote::factory()->create([
            'course_material_id' => $this->material->id,
            'generated_by' => $this->approvedStudent->id,
        ]);

        Bookmark::create([
            'user_id' => $this->approvedStudent->id,
            'bookmarkable_type' => GeneratedNote::class,
            'bookmarkable_id' => $note->id,
        ]);

        $response = $this->actingAs($this->approvedStudent)
            ->getJson('/api/v1/bookmarks?bookmarkable_type=' . urlencode(GeneratedNote::class));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.bookmarkable_type', GeneratedNote::class);
    }

    public function test_student_can_view_their_own_bookmark()
    {
        $bookmark = Bookmark::create([
            'user_id' => $this->approvedStudent->id,
            'bookmarkable_type' => CourseMaterial::class,
            'bookmarkable_id' => $this->material->id,
        ]);

        $response = $this->actingAs($this->approvedStudent)
            ->getJson("/api/v1/bookmarks/{$bookmark->id}");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $bookmark->id,
                    'user_id' => $this->approvedStudent->id,
                ],
            ]);
    }

    public function test_student_cannot_view_another_students_bookmark()
    {
        $bookmark = Bookmark::create([
            'user_id' => $this->otherStudent->id,
            'bookmarkable_type' => CourseMaterial::class,
            'bookmarkable_id' => $this->material->id,
        ]);

        $response = $this->actingAs($this->approvedStudent)
            ->getJson("/api/v1/bookmarks/{$bookmark->id}");

        $response->assertForbidden();
    }

    public function test_student_can_delete_their_own_bookmark()
    {
        $bookmark = Bookmark::create([
            'user_id' => $this->approvedStudent->id,
            'bookmarkable_type' => CourseMaterial::class,
            'bookmarkable_id' => $this->material->id,
        ]);

        $response = $this->actingAs($this->approvedStudent)
            ->deleteJson("/api/v1/bookmarks/{$bookmark->id}");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Bookmark deleted successfully.',
            ]);

        $this->assertDatabaseMissing('bookmarks', ['id' => $bookmark->id]);
    }

    public function test_student_cannot_delete_another_students_bookmark()
    {
        $bookmark = Bookmark::create([
            'user_id' => $this->otherStudent->id,
            'bookmarkable_type' => CourseMaterial::class,
            'bookmarkable_id' => $this->material->id,
        ]);

        $response = $this->actingAs($this->approvedStudent)
            ->deleteJson("/api/v1/bookmarks/{$bookmark->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('bookmarks', ['id' => $bookmark->id]);
    }

    public function test_bookmarks_respect_pagination()
    {
        for ($i = 0; $i < 25; $i++) {
            $material = CourseMaterial::factory()->create([
                'course_offering_id' => $this->material->course_offering_id,
                'status' => 'published',
            ]);

            $note = GeneratedNote::factory()->create([
                'course_material_id' => $material->id,
                'generated_by' => $this->approvedStudent->id,
            ]);

            Bookmark::create([
                'user_id' => $this->approvedStudent->id,
                'bookmarkable_type' => GeneratedNote::class,
                'bookmarkable_id' => $note->id,
            ]);
        }

        $response = $this->actingAs($this->approvedStudent)
            ->getJson('/api/v1/bookmarks?per_page=10');

        $response->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('pagination.per_page', 10)
            ->assertJsonPath('pagination.total', 25);
    }
}
