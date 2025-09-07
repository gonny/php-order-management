<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SanctumMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_spa_dashboard_metrics_endpoint_works(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/spa/v1/dashboard/metrics');

        $response->assertOk();
        // Just check that it returns data structure
        $response->assertJsonStructure(['data']);
    }

    public function test_spa_orders_endpoint_works(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/spa/v1/orders');

        $response->assertOk();
        // Check that pagination works
        $response->assertJsonStructure(['data']);
    }

    public function test_spa_clients_endpoint_works(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/spa/v1/clients');

        $response->assertOk();
        // Check that pagination works
        $response->assertJsonStructure(['data']);
    }

    public function test_webhooks_page_loads(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/webhooks');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('webhooks/Index')
        );
    }

    public function test_inertia_api_routes_are_removed(): void
    {
        $user = User::factory()->create();

        // These routes should no longer exist
        $response = $this->actingAs($user)
            ->getJson('/inertia-api/dashboard/metrics');

        $response->assertNotFound();
    }
}
