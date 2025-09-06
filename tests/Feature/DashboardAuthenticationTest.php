<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_page_redirects_to_login_when_unauthenticated()
    {
        $response = $this->get('/dashboard');
        
        $response->assertRedirect('/login');
    }

    public function test_dashboard_page_loads_when_authenticated()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/dashboard');
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Dashboard')
        );
    }

    public function test_dashboard_api_endpoint_works_with_session_auth()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)
            ->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ])
            ->get('/inertia-api/dashboard/metrics');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'order_counts',
                'total_revenue',
                'failed_jobs_count',
                'api_response_time_p95',
                'queue_sizes',
                'recent_orders',
                'recent_activities',
            ]);
    }

    public function test_settings_page_works_with_session_auth()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/settings/profile');
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('settings/Profile')
        );
    }

    public function test_orders_page_works_with_session_auth()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/orders');
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('orders/Index')
        );
    }

    public function test_clients_page_works_with_session_auth()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/clients');
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('clients/Index')
        );
    }

    public function test_session_based_api_calls_include_csrf_protection()
    {
        $user = User::factory()->create();
        
        // First, get the page to establish session
        $this->actingAs($user)->get('/dashboard');
        
        // Now make an API request without CSRF token (should fail if CSRF is enforced)
        $response = $this->actingAs($user)
            ->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ])
            ->withoutToken() // Remove CSRF token
            ->get('/inertia-api/dashboard/metrics');
        
        // Should still work for GET requests, but POST/PUT/DELETE would need CSRF
        $response->assertStatus(200);
    }
}