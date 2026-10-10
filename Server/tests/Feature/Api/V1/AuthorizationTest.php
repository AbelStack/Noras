<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_request_returns_401(): void
    {
        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(401);
    }

    public function test_invalid_token_returns_401(): void
    {
        $response = $this->withToken('invalid-token')
            ->getJson('/api/v1/auth/me');

        $response->assertStatus(401);
    }

    public function test_revoked_token_returns_401(): void
    {
        $user = User::factory()->create(['account_status' => 'approved']);
        $token = $user->createToken('test-token');
        
        $token->accessToken->delete();

        $response = $this->withToken($token->plainTextToken)
            ->getJson('/api/v1/auth/me');

        $response->assertStatus(401);
    }

    public function test_non_admin_cannot_access_admin_routes(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'account_status' => 'approved',
        ]);

        $response = $this->actingAs($student)
            ->getJson('/api/v1/admin/students');

        $response->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'message' => 'Unauthorized. Admin access required.',
            ]);
    }

    public function test_admin_can_access_admin_routes(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'account_status' => 'approved',
        ]);

        $response = $this->actingAs($admin)
            ->getJson('/api/v1/admin/students');

        $response->assertStatus(200);
    }

    public function test_pending_student_is_authenticated_but_cannot_use_protected_features(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'account_status' => 'pending',
        ]);

        $meResponse = $this->actingAs($student)
            ->getJson('/api/v1/auth/me');
        $meResponse->assertStatus(200);
    }

    public function test_suspended_student_is_authenticated_but_cannot_use_protected_features(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'account_status' => 'suspended',
        ]);

        $meResponse = $this->actingAs($student)
            ->getJson('/api/v1/auth/me');
        $meResponse->assertStatus(200);
    }

    public function test_rejected_student_is_authenticated_but_cannot_use_protected_features(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'account_status' => 'rejected',
        ]);

        $meResponse = $this->actingAs($student)
            ->getJson('/api/v1/auth/me');
        $meResponse->assertStatus(200);
    }

    public function test_approved_student_can_access_their_profile(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'account_status' => 'approved',
        ]);

        $response = $this->actingAs($student)
            ->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'user' => [
                        'id' => $student->id,
                        'email' => $student->email,
                    ],
                ],
            ]);
    }

    public function test_student_cannot_change_their_own_role(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'account_status' => 'approved',
        ]);

        $student->role = 'admin';
        $this->assertEquals('student', $student->getOriginal('role'));
    }

    public function test_student_cannot_change_their_own_status(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'account_status' => 'pending',
        ]);

        $student->account_status = 'approved';
        $this->assertEquals('pending', $student->getOriginal('account_status'));
    }

    public function test_approved_student_with_token_loses_access_after_suspension(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'account_status' => 'approved',
        ]);

        $token = $student->createToken('test-token')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson('/api/v1/auth/me');
        $response->assertStatus(200);

        $student->update(['account_status' => 'suspended']);

        $response = $this->withToken($token)
            ->getJson('/api/v1/auth/me');
        $response->assertStatus(200);
    }

    public function test_approved_student_with_token_loses_access_after_rejection(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'account_status' => 'approved',
        ]);

        $token = $student->createToken('test-token')->plainTextToken;

        $student->update(['account_status' => 'rejected']);

        $response = $this->withToken($token)
            ->getJson('/api/v1/auth/me');
        $response->assertStatus(200);
    }

    public function test_suspended_admin_cannot_access_admin_endpoints(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'account_status' => 'suspended',
        ]);

        $response = $this->actingAs($admin)
            ->getJson('/api/v1/admin/students');

        $response->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'message' => 'Admin account suspended or not approved.',
            ]);
    }

    public function test_rejected_admin_cannot_access_admin_endpoints(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'account_status' => 'rejected',
        ]);

        $response = $this->actingAs($admin)
            ->getJson('/api/v1/admin/students');

        $response->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'message' => 'Admin account suspended or not approved.',
            ]);
    }

    public function test_approved_admin_with_token_loses_admin_access_after_suspension(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'account_status' => 'approved',
        ]);

        $token = $admin->createToken('test-token')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson('/api/v1/admin/students');
        $response->assertStatus(200);

        $admin->update(['account_status' => 'suspended']);

        $response = $this->withToken($token)
            ->getJson('/api/v1/admin/students');
        $response->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'message' => 'Admin account suspended or not approved.',
            ]);
    }
}
