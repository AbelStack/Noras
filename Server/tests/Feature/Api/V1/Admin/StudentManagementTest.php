<?php

namespace Tests\Feature\Api\V1\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentManagementTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin()
    {
        return User::factory()->create([
            'role' => 'admin',
            'account_status' => 'approved',
        ]);
    }

    private function createStudent($status = 'pending', $overrides = [])
    {
        return User::factory()->create(array_merge([
            'role' => 'student',
            'account_status' => $status,
        ], $overrides));
    }

    public function test_unauthenticated_cannot_list_students(): void
    {
        $response = $this->getJson('/api/v1/admin/students');
        $response->assertStatus(401);
    }

    public function test_non_admin_cannot_list_students(): void
    {
        $student = $this->createStudent('approved');
        $response = $this->actingAs($student)->getJson('/api/v1/admin/students');
        $response->assertStatus(403);
    }

    public function test_admin_can_list_students_with_pagination(): void
    {
        $admin = $this->createAdmin();
        
        for ($i = 0; $i < 25; $i++) {
            $this->createStudent('pending');
        }

        $response = $this->actingAs($admin)
            ->getJson('/api/v1/admin/students?per_page=10');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'students',
                    'pagination' => [
                        'current_page',
                        'per_page',
                        'total',
                        'last_page',
                    ],
                ],
            ])
            ->assertJsonPath('data.pagination.per_page', 10)
            ->assertJsonPath('data.pagination.total', 25);

        $this->assertCount(10, $response->json('data.students'));
    }

    public function test_admin_can_search_students_by_name(): void
    {
        $admin = $this->createAdmin();
        $john = $this->createStudent('pending', ['name' => 'John Doe']);
        $jane = $this->createStudent('pending', ['name' => 'Jane Smith']);

        $response = $this->actingAs($admin)
            ->getJson('/api/v1/admin/students?search=John');

        $response->assertStatus(200);
        $students = $response->json('data.students');
        
        $this->assertCount(1, $students);
        $this->assertEquals($john->id, $students[0]['id']);
    }

    public function test_admin_can_search_students_by_email(): void
    {
        $admin = $this->createAdmin();
        $student = $this->createStudent('pending', ['email' => 'unique@example.com']);
        $this->createStudent('pending', ['email' => 'other@example.com']);

        $response = $this->actingAs($admin)
            ->getJson('/api/v1/admin/students?search=unique');

        $response->assertStatus(200);
        $students = $response->json('data.students');
        
        $this->assertCount(1, $students);
        $this->assertEquals($student->id, $students[0]['id']);
    }

    public function test_admin_can_search_students_by_student_id(): void
    {
        $admin = $this->createAdmin();
        $student = $this->createStudent('pending', ['student_id' => 'ST12345']);
        $this->createStudent('pending', ['student_id' => 'ST67890']);

        $response = $this->actingAs($admin)
            ->getJson('/api/v1/admin/students?search=ST123');

        $response->assertStatus(200);
        $students = $response->json('data.students');
        
        $this->assertCount(1, $students);
        $this->assertEquals($student->id, $students[0]['id']);
    }

    public function test_admin_can_filter_students_by_status(): void
    {
        $admin = $this->createAdmin();
        $this->createStudent('pending');
        $this->createStudent('pending');
        $this->createStudent('approved');
        $this->createStudent('rejected');

        $response = $this->actingAs($admin)
            ->getJson('/api/v1/admin/students?status=pending');

        $response->assertStatus(200);
        $students = $response->json('data.students');
        
        $this->assertCount(2, $students);
        foreach ($students as $student) {
            $this->assertEquals('pending', $student['account_status']);
        }
    }

    public function test_admin_can_combine_search_and_filter(): void
    {
        $admin = $this->createAdmin();
        $this->createStudent('pending', ['name' => 'John Pending']);
        $this->createStudent('approved', ['name' => 'John Approved']);
        $this->createStudent('pending', ['name' => 'Jane Pending']);

        $response = $this->actingAs($admin)
            ->getJson('/api/v1/admin/students?status=pending&search=John');

        $response->assertStatus(200);
        $students = $response->json('data.students');
        
        $this->assertCount(1, $students);
        $this->assertEquals('John Pending', $students[0]['name']);
        $this->assertEquals('pending', $students[0]['account_status']);
    }

    public function test_pagination_per_page_is_capped_at_100(): void
    {
        $admin = $this->createAdmin();
        
        for ($i = 0; $i < 150; $i++) {
            $this->createStudent('pending');
        }

        $response = $this->actingAs($admin)
            ->getJson('/api/v1/admin/students?per_page=200');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['per_page']);
    }

    public function test_max_valid_per_page_value_works(): void
    {
        $admin = $this->createAdmin();
        
        for ($i = 0; $i < 150; $i++) {
            $this->createStudent('pending');
        }

        $response = $this->actingAs($admin)
            ->getJson('/api/v1/admin/students?per_page=100');

        $response->assertStatus(200)
            ->assertJsonPath('data.pagination.per_page', 100);
    }

    public function test_invalid_status_filter_is_rejected(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->getJson('/api/v1/admin/students?status=invalid');

        $response->assertStatus(422);
    }

    public function test_student_list_does_not_expose_sensitive_fields(): void
    {
        $admin = $this->createAdmin();
        $student = $this->createStudent('pending');

        $response = $this->actingAs($admin)
            ->getJson('/api/v1/admin/students');

        $response->assertStatus(200);
        $studentData = $response->json('data.students.0');
        
        $this->assertArrayNotHasKey('password', $studentData);
        $this->assertArrayNotHasKey('remember_token', $studentData);
        $this->assertArrayHasKey('id', $studentData);
        $this->assertArrayHasKey('name', $studentData);
        $this->assertArrayHasKey('email', $studentData);
        $this->assertArrayHasKey('student_id', $studentData);
        $this->assertArrayHasKey('account_status', $studentData);
    }

    public function test_unauthenticated_cannot_view_student(): void
    {
        $student = $this->createStudent('pending');
        $response = $this->getJson("/api/v1/admin/students/{$student->id}");
        $response->assertStatus(401);
    }

    public function test_non_admin_cannot_view_student(): void
    {
        $student = $this->createStudent('approved');
        $otherStudent = $this->createStudent('pending');
        
        $response = $this->actingAs($student)
            ->getJson("/api/v1/admin/students/{$otherStudent->id}");
        
        $response->assertStatus(403);
    }

    public function test_admin_can_view_student_details(): void
    {
        $admin = $this->createAdmin();
        $student = $this->createStudent('approved');

        $response = $this->actingAs($admin)
            ->getJson("/api/v1/admin/students/{$student->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'student' => [
                        'id',
                        'name',
                        'email',
                        'student_id',
                        'role',
                        'account_status',
                        'approved_at',
                        'approved_by',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ])
            ->assertJsonPath('data.student.id', $student->id)
            ->assertJsonPath('data.student.email', $student->email);
    }

    public function test_admin_cannot_view_nonexistent_student(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->getJson('/api/v1/admin/students/99999');

        $response->assertStatus(404);
    }

    public function test_admin_cannot_view_admin_user_via_student_endpoint(): void
    {
        $admin = $this->createAdmin();
        $otherAdmin = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->getJson("/api/v1/admin/students/{$otherAdmin->id}");

        $response->assertStatus(404);
    }

    public function test_student_detail_does_not_expose_password(): void
    {
        $admin = $this->createAdmin();
        $student = $this->createStudent('pending');

        $response = $this->actingAs($admin)
            ->getJson("/api/v1/admin/students/{$student->id}");

        $response->assertStatus(200);
        $studentData = $response->json('data.student');
        
        $this->assertArrayNotHasKey('password', $studentData);
        $this->assertArrayNotHasKey('remember_token', $studentData);
    }
}
