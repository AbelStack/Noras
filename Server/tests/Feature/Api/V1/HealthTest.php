<?php

namespace Tests\Feature\Api\V1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_endpoint_returns_success(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'database',
                'timestamp',
            ])
            ->assertJson([
                'status' => 'ok',
                'message' => 'Application is running',
                'database' => 'connected',
            ]);
    }

    public function test_invalid_route_returns_404(): void
    {
        $response = $this->getJson('/api/v1/nonexistent');

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'message' => 'Resource not found',
            ]);
    }

    public function test_invalid_method_returns_405(): void
    {
        $response = $this->postJson('/api/v1/health');

        $response->assertStatus(405)
            ->assertJson([
                'status' => 'error',
                'message' => 'Method not allowed',
            ]);
    }
}
