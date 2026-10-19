<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use App\Models\University;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_successfully(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test Student',
            'email' => 'student@test.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'student_id' => 'WU2024001',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Registration successful. Your account is pending approval.',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'student@test.com',
            'student_id' => 'WU2024001',
            'role' => 'student',
            'account_status' => 'pending',
        ]);
    }

    public function test_public_can_fetch_campuses_and_departments_for_registration(): void
    {
        $campus = University::factory()->create(['name' => 'Kombolcha Campus', 'is_active' => true]);
        $dept = Department::factory()->create(['university_id' => $campus->id, 'name' => 'Computer Science', 'is_active' => true]);

        // Public can list campuses
        $campusRes = $this->getJson('/api/v1/auth/campuses');
        $campusRes->assertOk()
            ->assertJsonStructure(['status', 'data' => ['campuses']]);

        // Public can list departments filtered by campus
        $deptRes = $this->getJson('/api/v1/auth/departments?university_id=' . $campus->id);
        $deptRes->assertOk()
            ->assertJsonStructure(['status', 'data' => ['departments']]);
        $this->assertCount(1, $deptRes->json('data.departments'));
    }

    public function test_user_can_register_with_campus_and_department(): void
    {
        $campus = University::factory()->create(['name' => 'Dessie Campus', 'is_active' => true]);
        $dept = Department::factory()->create(['university_id' => $campus->id, 'name' => 'Software Engineering', 'is_active' => true]);

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Campus Student',
            'email' => 'campus_student@test.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'student_id' => 'WU2024999',
            'university_id' => $campus->id,
            'department_id' => $dept->id,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'user' => [
                        'university_id' => $campus->id,
                        'department_id' => $dept->id,
                    ],
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'campus_student@test.com',
            'university_id' => $campus->id,
            'department_id' => $dept->id,
        ]);
    }

    public function test_registration_validates_required_fields(): void
    {
        $response = $this->postJson('/api/v1/auth/register', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password', 'student_id']);
    }

    public function test_registration_validates_unique_email(): void
    {
        User::factory()->create(['email' => 'existing@test.com']);

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test Student',
            'email' => 'existing@test.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'student_id' => 'WU2024001',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_registration_validates_unique_student_id(): void
    {
        User::factory()->create(['student_id' => 'WU2024001']);

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test Student',
            'email' => 'student@test.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'student_id' => 'WU2024001',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['student_id']);
    }

    public function test_registration_cannot_create_admin_role(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'user@test.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'student_id' => 'WU2024001',
            'role' => 'admin',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('users', [
            'email' => 'user@test.com',
            'role' => 'student',
        ]);
    }

    public function test_approved_user_can_login(): void
    {
        $user = User::factory()->create([
            'email' => 'approved@test.com',
            'password' => 'password123',
            'account_status' => 'approved',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'approved@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['token', 'user'],
            ]);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'user@test.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'user@test.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'message' => 'Password is incorrect. Please check your password and try again.',
            ]);
    }

    public function test_pending_user_cannot_login(): void
    {
        User::factory()->create([
            'email' => 'pending@test.com',
            'password' => 'password123',
            'account_status' => 'pending',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'pending@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'message' => 'Your account is pending approval',
            ]);
    }

    public function test_rejected_user_cannot_login(): void
    {
        User::factory()->create([
            'email' => 'rejected@test.com',
            'password' => 'password123',
            'account_status' => 'rejected',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'rejected@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'message' => 'Your account has been rejected',
            ]);
    }

    public function test_suspended_user_cannot_login(): void
    {
        User::factory()->create([
            'email' => 'suspended@test.com',
            'password' => 'password123',
            'account_status' => 'suspended',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'suspended@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'message' => 'Your account has been suspended',
            ]);
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create(['account_status' => 'approved']);
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withToken($token)
            ->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Logout successful',
            ]);

        $this->assertCount(0, $user->tokens);
    }

    public function test_authenticated_user_can_access_me_endpoint(): void
    {
        $user = User::factory()->create(['account_status' => 'approved']);
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'email' => $user->email,
                    ],
                ],
            ]);
    }

    public function test_unauthenticated_user_cannot_access_me_endpoint(): void
    {
        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(401);
    }
}
