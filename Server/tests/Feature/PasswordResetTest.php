<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use App\Notifications\ResetPasswordNotification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_sends_reset_link_to_valid_email(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'test@example.com',
            'account_status' => 'approved',
        ]);

        $response = $this->postJson('/api/v1/password/forgot', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'If an account exists with that email, a password reset link has been sent.',
            ]);

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_forgot_password_returns_generic_response_for_non_existent_email(): void
    {
        Notification::fake();

        $response = $this->postJson('/api/v1/password/forgot', [
            'email' => 'nonexistent@example.com',
        ]);

        // Should return same success message to prevent email enumeration
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'If an account exists with that email, a password reset link has been sent.',
            ]);

        Notification::assertNothingSent();
    }

    public function test_forgot_password_requires_email(): void
    {
        $response = $this->postJson('/api/v1/password/forgot', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_forgot_password_requires_valid_email_format(): void
    {
        $response = $this->postJson('/api/v1/password/forgot', [
            'email' => 'not-an-email',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_reset_password_with_valid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('oldpassword'),
            'account_status' => 'approved',
        ]);

        $token = Password::createToken($user);

        $response = $this->postJson('/api/v1/password/reset', [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Your password has been reset successfully. You can now log in with your new password.',
            ]);

        // Verify password was actually changed
        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }

    public function test_reset_password_with_invalid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'account_status' => 'approved',
        ]);

        $response = $this->postJson('/api/v1/password/reset', [
            'token' => 'invalid-token',
            'email' => 'test@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'status' => 'error',
            ]);
    }

    public function test_reset_password_with_expired_token(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'account_status' => 'approved',
        ]);

        $token = Password::createToken($user);

        // Travel forward past expiration time (60 minutes by default)
        $this->travel(61)->minutes();

        $response = $this->postJson('/api/v1/password/reset', [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'status' => 'error',
            ]);
    }

    public function test_reset_password_token_cannot_be_reused(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('oldpassword'),
            'account_status' => 'approved',
        ]);

        $token = Password::createToken($user);

        // First reset - should succeed
        $response = $this->postJson('/api/v1/password/reset', [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(200);

        // Second reset with same token - should fail
        $response = $this->postJson('/api/v1/password/reset', [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'anotherpassword123',
            'password_confirmation' => 'anotherpassword123',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'status' => 'error',
            ]);
    }

    public function test_reset_password_requires_all_fields(): void
    {
        $response = $this->postJson('/api/v1/password/reset', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['token', 'email', 'password']);
    }

    public function test_reset_password_requires_password_confirmation(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'account_status' => 'approved',
        ]);

        $token = Password::createToken($user);

        $response = $this->postJson('/api/v1/password/reset', [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'newpassword123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_reset_password_requires_matching_confirmation(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'account_status' => 'approved',
        ]);

        $token = Password::createToken($user);

        $response = $this->postJson('/api/v1/password/reset', [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'differentpassword',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_reset_password_requires_minimum_length(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'account_status' => 'approved',
        ]);

        $token = Password::createToken($user);

        $response = $this->postJson('/api/v1/password/reset', [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_forgot_password_rate_limiting(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'account_status' => 'approved',
        ]);

        // Make 6 requests (limit is 5 per minute)
        for ($i = 0; $i < 6; $i++) {
            $response = $this->postJson('/api/v1/password/forgot', [
                'email' => 'test@example.com',
            ]);

            if ($i < 5) {
                $response->assertStatus(200);
            } else {
                $response->assertStatus(429); // Too Many Requests
            }
        }
    }

    public function test_reset_password_rate_limiting(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'account_status' => 'approved',
        ]);

        $token = Password::createToken($user);

        // Make 6 requests (limit is 5 per minute)
        for ($i = 0; $i < 6; $i++) {
            $response = $this->postJson('/api/v1/password/reset', [
                'token' => $token,
                'email' => 'test@example.com',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

            if ($i < 5) {
                // First one succeeds, rest fail with invalid token (already used)
                $this->assertContains($response->status(), [200, 400]);
            } else {
                $response->assertStatus(429); // Too Many Requests
            }
        }
    }
}
