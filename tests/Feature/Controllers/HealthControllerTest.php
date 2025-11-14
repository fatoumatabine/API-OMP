<?php

namespace Tests\Feature\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_endpoint_returns_ok(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'ok');
        $response->assertJsonStructure([
            'status',
            'timestamp',
            'uptime',
            'database',
            'version'
        ]);
    }

    public function test_health_endpoint_checks_database(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertStatus(200);
        $response->assertJsonPath('database', true);
    }

    public function test_health_endpoint_returns_version(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertStatus(200);
        $this->assertNotNull($response->json('version'));
    }

    public function test_health_detailed_endpoint(): void
    {
        $response = $this->getJson('/api/health/detailed');

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'ok');
        $response->assertJsonStructure([
            'status',
            'checks' => [
                'database',
                'cache',
                'disk'
            ],
            'timestamp',
            'version'
        ]);
    }

    public function test_health_detailed_checks_all_services(): void
    {
        $response = $this->getJson('/api/health/detailed');

        $response->assertStatus(200);
        $response->assertJsonPath('checks.database', true);
        $response->assertJsonPath('checks.cache', true);
        $response->assertJsonPath('checks.disk', true);
    }

    public function test_health_endpoint_is_public(): void
    {
        // Health endpoint should not require authentication
        $response = $this->getJson('/api/health');

        $this->assertNotEquals(401, $response->status());
    }

    public function test_health_uptime_is_integer(): void
    {
        $response = $this->getJson('/api/health');

        $uptime = $response->json('uptime');
        $this->assertIsInt($uptime);
        $this->assertGreaterThanOrEqual(0, $uptime);
    }

    public function test_health_timestamp_is_valid_iso8601(): void
    {
        $response = $this->getJson('/api/health');

        $timestamp = $response->json('timestamp');
        // Try to parse as ISO8601
        $parsed = \DateTime::createFromFormat(\DateTime::ATOM, $timestamp);
        $this->assertNotFalse($parsed);
    }
}
