<?php

namespace Tests\Feature\Api\V1\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentStatusSecurityTest extends TestCase
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

    public function test_student_cannot_approve_another_student(): void
    {
        $student = $this->createStudent('approved');
        $pendingStudent = $this->createStudent('pending');

        $response = $this->actingAs($student)
            ->postJson("/api/v1/admin/students/{$pendingStudent->id}/approve");

        $response->assertStatus(403);
        
        $this->assertDatabaseHas('users', [
            'id' => $pendingStudent->id,
            'account_status' => 'pending',
        ]);
    }

    public function test_student_cannot_reject_another_student(): void
    {
        $student = $this->createStudent('approved');
        $otherStudent = $this->createStudent('pending');

        $response = $this->actingAs($student)
            ->postJson("/api/v1/admin/students/{$otherStudent->id}/reject");

        $response->assertStatus(403);
    }

    public function test_student_cannot_suspend_another_student(): void
    {
        $student = $this->createStudent('approved');
        $otherStudent = $this->createStudent('approved');

        $response = $this->actingAs($student)
            ->postJson("/api/v1/admin/students/{$otherStudent->id}/suspend");

        $response->assertStatus(403);
    }

    public function test_student_cannot_modify_their_own_status(): void
    {
        $student = $this->createStudent('pending');

        $response = $this->actingAs($student)
            ->postJson("/api/v1/admin/students/{$student->id}/approve");

        $response->assertStatus(403);
        
        $this->assertDatabaseHas('users', [
            'id' => $student->id,
            'account_status' => 'pending',
        ]);
    }

    public function test_admin_cannot_modify_another_admin_via_student_endpoint(): void
    {
        $admin = $this->createAdmin();
        $otherAdmin = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->postJson("/api/v1/admin/students/{$otherAdmin->id}/suspend");

        $response->assertStatus(404);
        
        $this->assertDatabaseHas('users', [
            'id' => $otherAdmin->id,
            'account_status' => 'approved',
        ]);
    }

    public function test_suspended_admin_cannot_modify_student_status(): void
    {
        $admin = $this->createAdmin();
        $admin->update(['account_status' => 'suspended']);
        
        $student = $this->createStudent('pending');

        $response = $this->actingAs($admin)
            ->postJson("/api/v1/admin/students/{$student->id}/approve");

        $response->assertStatus(403);
    }

    public function test_rejected_admin_cannot_access_admin_endpoints(): void
    {
        $admin = $this->createAdmin();
        $admin->update(['account_status' => 'rejected']);
        
        $student = $this->createStudent('pending');

        $response = $this->actingAs($admin)
            ->getJson('/api/v1/admin/students');

        $response->assertStatus(403);
    }

    public function test_approving_student_records_admin_id(): void
    {
        $admin = $this->createAdmin();
        $student = $this->createStudent('pending');

        $this->actingAs($admin)
            ->postJson("/api/v1/admin/students/{$student->id}/approve");

        $this->assertDatabaseHas('users', [
            'id' => $student->id,
            'account_status' => 'approved',
            'approved_by' => $admin->id,
        ]);
    }

    public function test_rejecting_student_clears_approval_data(): void
    {
        $admin = $this->createAdmin();
        $student = $this->createStudent('approved');
        $student->update(['approved_by' => $admin->id]);

        $this->actingAs($admin)
            ->postJson("/api/v1/admin/students/{$student->id}/reject");

        $this->assertDatabaseHas('users', [
            'id' => $student->id,
            'account_status' => 'rejected',
            'approved_by' => null,
        ]);
    }

    public function test_student_cannot_change_their_role_to_admin(): void
    {
        $student = $this->createStudent('approved');

        $response = $this->actingAs($student)
            ->putJson("/api/v1/auth/me", ['role' => 'admin']);

        $this->assertDatabaseHas('users', [
            'id' => $student->id,
            'role' => 'student',
        ]);
    }

    public function test_suspended_student_tokens_are_revoked(): void
    {
        $admin = $this->createAdmin();
        $student = $this->createStudent('approved');
        $student->createToken('test');

        $this->assertCount(1, $student->tokens);

        $this->actingAs($admin)
            ->postJson("/api/v1/admin/students/{$student->id}/suspend");

        $this->assertCount(0, $student->fresh()->tokens);
    }

    public function test_rejected_student_cannot_access_protected_routes(): void
    {
        $admin = $this->createAdmin();
        $student = $this->createStudent('approved');

        $this->actingAs($admin)
            ->postJson("/api/v1/admin/students/{$student->id}/reject");

        $response = $this->actingAs($student)
            ->getJson('/api/v1/universities');

        $response->assertStatus(403);
    }

    public function test_reactivated_student_can_access_protected_routes(): void
    {
        $admin = $this->createAdmin();
        $student = $this->createStudent('suspended');

        $this->actingAs($admin)
            ->postJson("/api/v1/admin/students/{$student->id}/reactivate");

        $response = $this->actingAs($student)
            ->getJson('/api/v1/universities');

        $response->assertStatus(200);
    }

    public function test_cannot_approve_nonexistent_student(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->postJson('/api/v1/admin/students/99999/approve');

        $response->assertStatus(404);
    }

    public function test_status_transitions_use_database_transactions(): void
    {
        $admin = $this->createAdmin();
        $student = $this->createStudent('pending');

        $response = $this->actingAs($admin)
            ->postJson("/api/v1/admin/students/{$student->id}/approve");

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('users', [
            'id' => $student->id,
            'account_status' => 'approved',
            'approved_by' => $admin->id,
        ]);

        $this->assertNotNull($student->fresh()->approved_at);
    }
}
