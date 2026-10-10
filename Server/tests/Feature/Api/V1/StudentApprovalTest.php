<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentApprovalTest extends TestCase
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

    public function test_admin_can_list_all_students(): void
    {
        $admin = $this->createAdmin();
        $this->createStudent('pending');
        $this->createStudent('approved');

        $response = $this->actingAs($admin)
            ->getJson('/api/v1/admin/students');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => ['students'],
            ]);
    }

    public function test_admin_can_filter_students_by_status(): void
    {
        $admin = $this->createAdmin();
        $this->createStudent('pending');
        $this->createStudent('approved');

        $response = $this->actingAs($admin)
            ->getJson('/api/v1/admin/students?status=pending');

        $response->assertStatus(200);
        
        $students = $response->json('data.students');
        $this->assertCount(1, $students);
        $this->assertEquals('pending', $students[0]['account_status']);
    }

    public function test_admin_can_approve_pending_student(): void
    {
        $admin = $this->createAdmin();
        $student = $this->createStudent('pending');

        $response = $this->actingAs($admin)
            ->postJson("/api/v1/admin/students/{$student->id}/approve");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Student approved successfully',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $student->id,
            'account_status' => 'approved',
            'approved_by' => $admin->id,
        ]);
    }

    public function test_admin_cannot_approve_already_approved_student(): void
    {
        $admin = $this->createAdmin();
        $student = $this->createStudent('approved');

        $response = $this->actingAs($admin)
            ->postJson("/api/v1/admin/students/{$student->id}/approve");

        $response->assertStatus(400)
            ->assertJson([
                'status' => 'error',
                'message' => 'Student is already approved',
            ]);
    }

    public function test_admin_can_reject_student(): void
    {
        $admin = $this->createAdmin();
        $student = $this->createStudent('pending');

        $response = $this->actingAs($admin)
            ->postJson("/api/v1/admin/students/{$student->id}/reject");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Student rejected successfully',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $student->id,
            'account_status' => 'rejected',
            'approved_by' => null,
        ]);
    }

    public function test_rejecting_student_revokes_all_tokens(): void
    {
        $admin = $this->createAdmin();
        $student = $this->createStudent('approved');
        $student->createToken('test-token');

        $this->assertCount(1, $student->tokens);

        $this->actingAs($admin)
            ->postJson("/api/v1/admin/students/{$student->id}/reject");

        $this->assertCount(0, $student->fresh()->tokens);
    }

    public function test_admin_can_suspend_student(): void
    {
        $admin = $this->createAdmin();
        $student = $this->createStudent('approved');

        $response = $this->actingAs($admin)
            ->postJson("/api/v1/admin/students/{$student->id}/suspend");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Student suspended successfully',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $student->id,
            'account_status' => 'suspended',
        ]);
    }

    public function test_suspending_student_revokes_all_tokens(): void
    {
        $admin = $this->createAdmin();
        $student = $this->createStudent('approved');
        $student->createToken('test-token');

        $this->assertCount(1, $student->tokens);

        $this->actingAs($admin)
            ->postJson("/api/v1/admin/students/{$student->id}/suspend");

        $this->assertCount(0, $student->fresh()->tokens);
    }

    public function test_admin_can_reactivate_suspended_student(): void
    {
        $admin = $this->createAdmin();
        $student = $this->createStudent('suspended');

        $response = $this->actingAs($admin)
            ->postJson("/api/v1/admin/students/{$student->id}/reactivate");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Student reactivated successfully',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $student->id,
            'account_status' => 'approved',
            'approved_by' => $admin->id,
        ]);
    }

    public function test_admin_can_reactivate_rejected_student(): void
    {
        $admin = $this->createAdmin();
        $student = $this->createStudent('rejected');

        $response = $this->actingAs($admin)
            ->postJson("/api/v1/admin/students/{$student->id}/reactivate");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Student reactivated successfully',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $student->id,
            'account_status' => 'approved',
        ]);
    }

    public function test_cannot_reactivate_pending_or_approved_student(): void
    {
        $admin = $this->createAdmin();
        $student = $this->createStudent('pending');

        $response = $this->actingAs($admin)
            ->postJson("/api/v1/admin/students/{$student->id}/reactivate");

        $response->assertStatus(400)
            ->assertJson([
                'status' => 'error',
                'message' => 'Only suspended or rejected accounts can be reactivated',
            ]);
    }

    public function test_student_cannot_access_admin_endpoints(): void
    {
        $student = $this->createStudent('approved');
        $otherStudent = $this->createStudent('pending');

        $response = $this->actingAs($student)
            ->postJson("/api/v1/admin/students/{$otherStudent->id}/approve");

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_admin_endpoints(): void
    {
        $student = $this->createStudent('pending');

        $response = $this->postJson("/api/v1/admin/students/{$student->id}/approve");

        $response->assertStatus(401);
    }
}
