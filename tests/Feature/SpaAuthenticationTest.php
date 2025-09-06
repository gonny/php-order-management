<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SpaAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test CSRF cookie endpoint
     */
    public function test_can_get_csrf_cookie(): void
    {
        $response = $this->getJson('/spa/v1/auth/csrf-cookie');

        $response->assertStatus(200)
                ->assertJson(['message' => 'CSRF cookie set']);
    }

    /**
     * Test user registration via SPA API
     */
    public function test_can_register_user_via_spa_api(): void
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/spa/v1/auth/register', $userData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'message',
                    'user' => ['id', 'name', 'email']
                ]);
        
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'Test User',
        ]);
    }

    /**
     * Test user login via SPA API
     */
    public function test_can_login_user_via_spa_api(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/spa/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'user' => ['id', 'name', 'email']
                ]);
    }

    /**
     * Test protected route requires authentication
     */
    public function test_protected_spa_routes_require_authentication(): void
    {
        $response = $this->getJson('/spa/v1/auth/user');

        $response->assertStatus(401);
    }

    /**
     * Test can access protected route when authenticated
     */
    public function test_can_access_protected_spa_routes_when_authenticated(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
                        ->getJson('/spa/v1/auth/user');

        $response->assertStatus(200)
                ->assertJson([
                    'user' => [
                        'id' => $user->id,
                        'email' => $user->email,
                    ]
                ]);
    }

    /**
     * Test logout functionality
     */
    public function test_can_logout_via_spa_api(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
                        ->postJson('/spa/v1/auth/logout');

        $response->assertStatus(200)
                ->assertJson(['message' => 'Logout successful']);
    }
}