<?php

namespace Tests\Feature\Api\V1\Admin;

use App\Models\Course;
use App\Models\CourseMaterial;
use App\Models\CourseOffering;
use App\Models\Department;
use App\Models\GeneratedNote;
use App\Models\GeneratedQuiz;
use App\Models\QuizQuestion;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminNotesAndQuizzesTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin()
    {
        return User::factory()->create(['role' => 'admin', 'account_status' => 'approved']);
    }

    private function createStudent()
    {
        return User::factory()->create(['role' => 'student', 'account_status' => 'approved']);
    }

    private function createCurriculumAndMaterial($admin)
    {
        $university = University::factory()->create(['name' => 'Main Campus']);
        $department = Department::factory()->create([
            'university_id' => $university->id,
            'name' => 'Computer Science',
        ]);
        $course = Course::factory()->create([
            'code' => 'CS301',
            'title' => 'Web Development',
        ]);
        $offering = CourseOffering::create([
            'course_id' => $course->id,
            'department_id' => $department->id,
            'academic_year' => 2026,
            'study_year' => 3,
            'semester' => '1',
        ]);

        $material = CourseMaterial::create([
            'course_offering_id' => $offering->id,
            'uploaded_by' => $admin->id,
            'title' => 'Lecture 1: Fullstack Architecture',
            'original_filename' => 'lecture1.pdf',
            'stored_filename' => 'stored_lecture1.pdf',
            'file_path' => 'materials/stored_lecture1.pdf',
            'file_size' => 102400,
            'mime_type' => 'application/pdf',
            'status' => 'published',
            'version' => 1,
        ]);

        return [$university, $department, $course, $offering, $material];
    }

    public function test_admin_can_list_notes_with_curriculum_filters(): void
    {
        $admin = $this->createAdmin();
        [$university, $department, $course, $offering, $material] = $this->createCurriculumAndMaterial($admin);

        $note = GeneratedNote::create([
            'course_material_id' => $material->id,
            'material_version' => 1,
            'generated_by' => $admin->id,
            'content' => 'Comprehensive master notes for Lecture 1',
            'ai_provider' => 'gemini',
            'ai_model' => 'gemini-1.5-flash',
            'source_char_count' => 5000,
            'notes_char_count' => 2500,
            'generation_time_ms' => 1200,
        ]);

        // Unfiltered
        $res = $this->actingAs($admin)->getJson('/api/v1/admin/notes');
        $res->assertStatus(200);
        $res->assertJsonFragment(['total_notes' => 1]);
        $this->assertCount(1, $res->json('data.notes'));

        // Filter by campus
        $resCampus = $this->actingAs($admin)->getJson('/api/v1/admin/notes?campus_id=' . $university->id);
        $resCampus->assertStatus(200);
        $this->assertCount(1, $resCampus->json('data.notes'));

        // Filter by non-matching study year
        $resYear = $this->actingAs($admin)->getJson('/api/v1/admin/notes?study_year=5');
        $resYear->assertStatus(200);
        $this->assertCount(0, $resYear->json('data.notes'));
    }

    public function test_admin_can_show_note(): void
    {
        $admin = $this->createAdmin();
        [$university, $department, $course, $offering, $material] = $this->createCurriculumAndMaterial($admin);

        $note = GeneratedNote::create([
            'course_material_id' => $material->id,
            'material_version' => 1,
            'generated_by' => $admin->id,
            'content' => 'Detailed study note text',
            'ai_provider' => 'gemini',
            'ai_model' => 'gemini-1.5-flash',
            'source_char_count' => 1000,
            'notes_char_count' => 500,
            'generation_time_ms' => 800,
        ]);

        $res = $this->actingAs($admin)->getJson('/api/v1/admin/notes/' . $note->id);
        $res->assertStatus(200);
        $res->assertJsonFragment(['content' => 'Detailed study note text']);
    }

    public function test_admin_can_delete_note(): void
    {
        $admin = $this->createAdmin();
        [$university, $department, $course, $offering, $material] = $this->createCurriculumAndMaterial($admin);

        $note = GeneratedNote::create([
            'course_material_id' => $material->id,
            'material_version' => 1,
            'generated_by' => $admin->id,
            'content' => 'Note to be deleted',
            'ai_provider' => 'gemini',
            'ai_model' => 'gemini-1.5-flash',
            'source_char_count' => 1000,
            'notes_char_count' => 500,
            'generation_time_ms' => 800,
        ]);

        $res = $this->actingAs($admin)->deleteJson('/api/v1/admin/notes/' . $note->id);
        $res->assertStatus(200);
        $this->assertDatabaseMissing('generated_notes', ['id' => $note->id]);
    }

    public function test_admin_can_list_quizzes_with_curriculum_filters(): void
    {
        $admin = $this->createAdmin();
        [$university, $department, $course, $offering, $material] = $this->createCurriculumAndMaterial($admin);

        $quiz = GeneratedQuiz::create([
            'course_material_id' => $material->id,
            'material_version' => 1,
            'generated_by' => $admin->id,
            'ai_provider' => 'gemini',
            'ai_model' => 'gemini-1.5-flash',
            'question_count' => 20,
            'source_char_count' => 6000,
            'generation_time_ms' => 1500,
        ]);

        $res = $this->actingAs($admin)->getJson('/api/v1/admin/quizzes');
        $res->assertStatus(200);
        $res->assertJsonFragment(['total_quizzes' => 1]);
        $this->assertCount(1, $res->json('data.quizzes'));

        // Search matching
        $resSearch = $this->actingAs($admin)->getJson('/api/v1/admin/quizzes?search=Fullstack');
        $resSearch->assertStatus(200);
        $this->assertCount(1, $resSearch->json('data.quizzes'));
    }

    public function test_admin_can_show_and_delete_quiz(): void
    {
        $admin = $this->createAdmin();
        [$university, $department, $course, $offering, $material] = $this->createCurriculumAndMaterial($admin);

        $quiz = GeneratedQuiz::create([
            'course_material_id' => $material->id,
            'material_version' => 1,
            'generated_by' => $admin->id,
            'ai_provider' => 'gemini',
            'ai_model' => 'gemini-1.5-flash',
            'question_count' => 20,
            'source_char_count' => 6000,
            'generation_time_ms' => 1500,
        ]);

        $q = QuizQuestion::create([
            'quiz_id' => $quiz->id,
            'question_text' => 'What is the role of the client?',
            'question_order' => 1,
            'explanation' => 'See page 2 for client definition',
        ]);

        $res = $this->actingAs($admin)->getJson('/api/v1/admin/quizzes/' . $quiz->id);
        $res->assertStatus(200);
        $res->assertJsonFragment(['question_text' => 'What is the role of the client?']);

        $del = $this->actingAs($admin)->deleteJson('/api/v1/admin/quizzes/' . $quiz->id);
        $del->assertStatus(200);
        $this->assertDatabaseMissing('generated_quizzes', ['id' => $quiz->id]);
    }

    public function test_student_cannot_access_admin_notes_or_quizzes(): void
    {
        $student = $this->createStudent();

        $this->actingAs($student)->getJson('/api/v1/admin/notes')->assertStatus(403);
        $this->actingAs($student)->getJson('/api/v1/admin/quizzes')->assertStatus(403);
    }
}
