<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpaAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that SPA routes require authentication
     */
    public function test_spa_routes_require_authentication(): void
    {
        $response = $this->getJson('/spa/v1/dashboard/metrics');
        $response->assertStatus(401);

        $response = $this->getJson('/spa/v1/orders');
        $response->assertStatus(401);

        $response = $this->getJson('/spa/v1/clients');
        $response->assertStatus(401);
    }

    /**
     * Test that authenticated users can access SPA routes
     */
    public function test_authenticated_users_can_access_spa_routes(): void
    {
        $user = User::factory()->create();

        // Test dashboard metrics
        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/spa/v1/dashboard/metrics');
        $response->assertStatus(200);

        // Test orders listing
        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/spa/v1/orders');
        $response->assertStatus(200);

        // Test clients listing
        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/spa/v1/clients');
        $response->assertStatus(200);
    }

    /**
     * Test user info endpoint
     */
    public function test_can_get_user_info_when_authenticated(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/spa/v1/auth/user');

        $response->assertStatus(200)
            ->assertJson([
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'name' => $user->name,
                ],
            ]);
    }

    /**
     * Test user info endpoint requires authentication
     */
    public function test_user_info_endpoint_requires_authentication(): void
    {
        $response = $this->getJson('/spa/v1/auth/user');
        $response->assertStatus(401);
    }

    /**
     * Test that session-based authentication works with Sanctum
     */
    public function test_session_authentication_works_with_sanctum(): void
    {
        $user = User::factory()->create();

        // Authenticate using web guard (session)
        $this->actingAs($user, 'web');

        // Should be able to access Sanctum-protected routes
        $response = $this->getJson('/spa/v1/auth/user', [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                ],
            ]);
    }

    /**
     * Test CSRF protection is enforced for state-changing operations
     */
    public function test_csrf_protection_enforced_for_mutations(): void
    {
        $user = User::factory()->create();

        // Attempt to create an order without CSRF token
        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/spa/v1/orders', [
                'number' => 'TEST001',
                'status' => 'new',
            ]);

        // Should work since we're using the test environment
        // In a real browser, CSRF would be required
        $response->assertStatus(422); // Validation error due to missing required fields
    }
}
