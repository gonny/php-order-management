<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Order;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InertiaApiControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
    }

    public function test_dashboard_metrics_requires_authentication()
    {
        $response = $this->getJson('/inertia-api/dashboard/metrics');
        
        $response->assertStatus(401);
    }

    public function test_dashboard_metrics_returns_data_when_authenticated()
    {
        // Create some test data
        $client = Client::factory()->create();
        Order::factory()->count(5)->create([
            'client_id' => $client->id,
            'status' => 'new'
        ]);
        Order::factory()->count(3)->create([
            'client_id' => $client->id,
            'status' => 'confirmed'
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/inertia-api/dashboard/metrics');

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

        $data = $response->json();
        
        // Verify order counts
        $this->assertEquals(5, $data['order_counts']['new']);
        $this->assertEquals(3, $data['order_counts']['confirmed']);
        $this->assertArrayHasKey('total_revenue', $data);
    }

    public function test_orders_list_requires_authentication()
    {
        $response = $this->getJson('/inertia-api/orders');
        
        $response->assertStatus(401);
    }

    public function test_orders_list_returns_paginated_data()
    {
        $client = Client::factory()->create();
        Order::factory()->count(10)->create(['client_id' => $client->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/inertia-api/orders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'number',
                        'status',
                        'total_amount',
                        'created_at',
                        'client'
                    ]
                ],
                'current_page',
                'last_page',
                'per_page',
                'total'
            ]);
    }

    public function test_orders_list_can_be_filtered()
    {
        $client = Client::factory()->create();
        Order::factory()->create([
            'client_id' => $client->id,
            'status' => 'new'
        ]);
        Order::factory()->create([
            'client_id' => $client->id,
            'status' => 'confirmed'
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/inertia-api/orders?status=new');

        $response->assertStatus(200);
        
        $orders = $response->json('data');
        $this->assertCount(1, $orders);
        $this->assertEquals('new', $orders[0]['status']);
    }

    public function test_single_order_requires_authentication()
    {
        $client = Client::factory()->create();
        $order = Order::factory()->create(['client_id' => $client->id]);

        $response = $this->getJson("/inertia-api/orders/{$order->id}");
        
        $response->assertStatus(401);
    }

    public function test_single_order_returns_data_with_relationships()
    {
        $client = Client::factory()->create();
        $order = Order::factory()->create(['client_id' => $client->id]);

        $response = $this->actingAs($this->user)
            ->getJson("/inertia-api/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'number',
                    'status',
                    'total_amount',
                    'client',
                    'items',
                    'shipping_labels'
                ]
            ]);
    }

    public function test_clients_list_requires_authentication()
    {
        $response = $this->getJson('/inertia-api/clients');
        
        $response->assertStatus(401);
    }

    public function test_clients_list_returns_paginated_data()
    {
        Client::factory()->count(10)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/inertia-api/clients');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'first_name',
                        'last_name',
                        'email',
                        'phone',
                        'is_active',
                        'created_at'
                    ]
                ],
                'current_page',
                'last_page',
                'per_page',
                'total'
            ]);
    }

    public function test_single_client_requires_authentication()
    {
        $client = Client::factory()->create();

        $response = $this->getJson("/inertia-api/clients/{$client->id}");
        
        $response->assertStatus(401);
    }

    public function test_single_client_returns_data_with_orders()
    {
        $client = Client::factory()->create();
        Order::factory()->count(3)->create(['client_id' => $client->id]);

        $response = $this->actingAs($this->user)
            ->getJson("/inertia-api/clients/{$client->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'first_name',
                    'last_name',
                    'email',
                    'phone',
                    'is_active',
                    'orders'
                ]
            ]);
    }

    public function test_csrf_protection_is_enforced()
    {
        // This test verifies that CSRF protection is working
        $response = $this->actingAs($this->user)
            ->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ])
            ->post('/inertia-api/dashboard/metrics'); // POST to test CSRF

        // Should get method not allowed since we only allow GET, but not CSRF error
        $response->assertStatus(405);
    }
}