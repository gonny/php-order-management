<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalhostSanctumTest extends TestCase
{
    use RefreshDatabase;

    public function test_sanctum_auth_works_for_localhost_8080()
    {
        $user = User::factory()->create();
        
        // Simulate a request from localhost:8080 with session auth
        $response = $this->actingAs($user)
            ->withHeaders([
                'Host' => 'localhost:8080',
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ])
            ->getJson('/spa/v1/dashboard/metrics');
            
        $response->assertStatus(200);
    }
    
    public function test_csrf_cookie_endpoint_works()
    {
        $response = $this->get('/sanctum/csrf-cookie');
        $response->assertStatus(204);
    }
    
    public function test_authentication_flow_with_csrf()
    {
        $user = User::factory()->create();
        
        // First get CSRF cookie
        $csrfResponse = $this->get('/sanctum/csrf-cookie');
        $csrfResponse->assertStatus(204);
        
        // Extract XSRF token from cookies
        $cookies = $csrfResponse->headers->getCookies();
        $xsrfToken = null;
        foreach ($cookies as $cookie) {
            if ($cookie->getName() === 'XSRF-TOKEN') {
                $xsrfToken = urldecode($cookie->getValue());
                break;
            }
        }
        
        // Now make authenticated request with proper headers
        $response = $this->actingAs($user)
            ->withHeaders([
                'Host' => 'localhost:8080',
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
                'X-CSRF-TOKEN' => $xsrfToken,
            ])
            ->getJson('/spa/v1/dashboard/metrics');
            
        $response->assertStatus(200);
    }
}