<?php

namespace Tests\Feature\Api\V1\Admin;

use App\Models\User;
use App\Models\University;
use App\Models\Department;
use App\Models\Course;
use App\Models\CourseOffering;
use App\Models\CourseMaterial;
use App\Models\GeneratedNote;
use App\Models\GeneratedQuiz;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin()
    {
        return User::factory()->create([
            'role' => 'admin',
            'account_status' => 'approved',
        ]);
    }

    private function createStudent($status = 'pending')
    {
        return User::factory()->create([
            'role' => 'student',
            'account_status' => $status,
        ]);
    }

    public function test_unauthenticated_cannot_access_dashboard(): void
    {
        $response = $this->getJson('/api/v1/admin/dashboard');
        $response->assertStatus(401);
    }

    public function test_non_admin_cannot_access_dashboard(): void
    {
        $student = $this->createStudent('approved');
        $response = $this->actingAs($student)->getJson('/api/v1/admin/dashboard');
        $response->assertStatus(403);
    }

    public function test_admin_can_view_dashboard_summary(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->getJson('/api/v1/admin/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'students' => [
                        'total',
                        'pending',
                        'approved',
                        'rejected',
                        'suspended',
                    ],
                    'catalog' => [
                        'universities',
                        'departments',
                        'courses',
                        'course_offerings',
                    ],
                    'materials' => [
                        'total',
                        'published',
                        'draft',
                    ],
                    'ai_generated' => [
                        'notes',
                        'quizzes',
                    ],
                ],
            ]);
    }

    public function test_dashboard_returns_correct_student_counts(): void
    {
        $admin = $this->createAdmin();
        
        $this->createStudent('pending');
        $this->createStudent('pending');
        $this->createStudent('approved');
        $this->createStudent('approved');
        $this->createStudent('approved');
        $this->createStudent('rejected');
        $this->createStudent('suspended');

        $response = $this->actingAs($admin)->getJson('/api/v1/admin/dashboard');

        $response->assertStatus(200)
            ->assertJsonPath('data.students.total', 7)
            ->assertJsonPath('data.students.pending', 2)
            ->assertJsonPath('data.students.approved', 3)
            ->assertJsonPath('data.students.rejected', 1)
            ->assertJsonPath('data.students.suspended', 1);
    }

    public function test_dashboard_returns_accurate_catalog_counts(): void
    {
        $admin = $this->createAdmin();
        
        $beforeData = $this->actingAs($admin)
            ->getJson('/api/v1/admin/dashboard')
            ->json('data.catalog');
        
        University::factory()->count(2)->create();
        Department::factory()->count(3)->create();
        Course::factory()->count(5)->create();
        CourseOffering::factory()->count(7)->create();

        $afterData = $this->actingAs($admin)
            ->getJson('/api/v1/admin/dashboard')
            ->json('data.catalog');

        $this->assertGreaterThanOrEqual($beforeData['universities'] + 2, $afterData['universities']);
        $this->assertGreaterThanOrEqual($beforeData['departments'] + 3, $afterData['departments']);
        $this->assertGreaterThanOrEqual($beforeData['courses'] + 5, $afterData['courses']);
        $this->assertGreaterThanOrEqual($beforeData['course_offerings'] + 7, $afterData['course_offerings']);
    }

    public function test_dashboard_returns_correct_material_counts(): void
    {
        $admin = $this->createAdmin();
        
        CourseMaterial::factory()->count(8)->create(['status' => 'published']);
        CourseMaterial::factory()->count(3)->create(['status' => 'draft']);

        $response = $this->actingAs($admin)->getJson('/api/v1/admin/dashboard');

        $response->assertStatus(200)
            ->assertJsonPath('data.materials.total', 11)
            ->assertJsonPath('data.materials.published', 8)
            ->assertJsonPath('data.materials.draft', 3);
    }

    public function test_dashboard_returns_correct_ai_generated_counts(): void
    {
        $admin = $this->createAdmin();
        
        GeneratedNote::factory()->count(12)->create();
        GeneratedQuiz::factory()->count(8)->create();

        $response = $this->actingAs($admin)->getJson('/api/v1/admin/dashboard');

        $response->assertStatus(200)
            ->assertJsonPath('data.ai_generated.notes', 12)
            ->assertJsonPath('data.ai_generated.quizzes', 8);
    }

    public function test_dashboard_does_not_count_admins_as_students(): void
    {
        $admin = $this->createAdmin();
        
        $this->createAdmin();
        $this->createAdmin();
        $this->createStudent('pending');
        $this->createStudent('approved');

        $response = $this->actingAs($admin)->getJson('/api/v1/admin/dashboard');

        $response->assertStatus(200)
            ->assertJsonPath('data.students.total', 2);
    }

    public function test_dashboard_returns_zero_for_empty_categories(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->getJson('/api/v1/admin/dashboard');

        $response->assertStatus(200)
            ->assertJsonPath('data.students.total', 0)
            ->assertJsonPath('data.students.pending', 0)
            ->assertJsonPath('data.students.approved', 0)
            ->assertJsonPath('data.students.rejected', 0)
            ->assertJsonPath('data.students.suspended', 0)
            ->assertJsonPath('data.catalog.universities', 0)
            ->assertJsonPath('data.catalog.departments', 0)
            ->assertJsonPath('data.catalog.courses', 0)
            ->assertJsonPath('data.catalog.course_offerings', 0)
            ->assertJsonPath('data.materials.total', 0)
            ->assertJsonPath('data.materials.published', 0)
            ->assertJsonPath('data.materials.draft', 0)
            ->assertJsonPath('data.ai_generated.notes', 0)
            ->assertJsonPath('data.ai_generated.quizzes', 0);
    }

    public function test_dashboard_does_not_expose_personal_information(): void
    {
        $admin = $this->createAdmin();
        $student = $this->createStudent('pending');

        $response = $this->actingAs($admin)->getJson('/api/v1/admin/dashboard');

        $response->assertStatus(200);
        $responseData = $response->json('data');
        
        $this->assertIsArray($responseData);
        $this->assertArrayNotHasKey('users', $responseData);
        $this->assertArrayNotHasKey('emails', $responseData);
        $this->assertArrayNotHasKey('passwords', $responseData);
    }
}
