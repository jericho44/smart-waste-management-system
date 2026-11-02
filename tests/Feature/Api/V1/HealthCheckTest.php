<?php

namespace Tests\Feature\Api\V1;

use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    public function test_health_check_returns_standard_api_envelope(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertStatus(200)
            ->assertHeader('X-Request-ID')
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'service',
                    'status',
                    'database',
                    'timestamp',
                ],
                'meta' => [
                    'request_id',
                    'timestamp',
                ],
                'errors',
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'service' => 'Smart Waste Management System',
                    'status' => 'operational',
                    'database' => 'connected',
                ],
                'errors' => null,
            ]);
    }

    public function test_unauthenticated_request_returns_standard_error_envelope(): void
    {
        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(401)
            ->assertHeader('X-Request-ID')
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'meta' => [
                    'request_id',
                    'timestamp',
                ],
                'errors',
            ])
            ->assertJson([
                'success' => false,
                'message' => 'Unauthenticated',
                'data' => null,
                'errors' => null,
            ]);
    }
}
