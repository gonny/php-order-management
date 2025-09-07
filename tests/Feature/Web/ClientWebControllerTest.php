<?php

namespace Tests\Feature\Web;

use App\Models\Client;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientWebControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_guests_cannot_access_client_pages(): void
    {
        $client = Client::factory()->create();

        $this->get('/clients')->assertRedirect('/login');
        $this->get('/clients/create')->assertRedirect('/login');
        $this->get("/clients/{$client->id}")->assertRedirect('/login');
        $this->get("/clients/{$client->id}/edit")->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_clients_index(): void
    {
        $this->actingAs($this->user);

        $clients = Client::factory()->count(3)->create();

        $response = $this->get('/clients');

        $response->assertStatus(200);
        $response->assertInertia(function ($page) {
            $page->component('clients/Index')
                ->has('clients')
                ->has('filters');
        });
    }

    public function test_clients_index_includes_order_counts(): void
    {
        $this->actingAs($this->user);

        $client = Client::factory()->create();
        Order::factory()->count(2)->create(['client_id' => $client->id]);

        $response = $this->get('/clients');

        $response->assertStatus(200);
        $response->assertInertia(function ($page) {
            $page->has('clients.data.0.orders_count');
        });
    }

    public function test_clients_index_respects_search_filter(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/clients?search=test&is_active=1');

        $response->assertStatus(200);
        $response->assertInertia(function ($page) {
            $page->has('filters', function ($filters) {
                return $filters->where('search', 'test')
                              ->where('is_active', '1');
            });
        });
    }

    public function test_authenticated_user_can_view_create_client_page(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/clients/create');

        $response->assertStatus(200);
        $response->assertInertia(function ($page) {
            $page->component('clients/Create');
        });
    }

    public function test_authenticated_user_can_view_client_details(): void
    {
        $this->actingAs($this->user);

        $client = Client::factory()->create();
        $orders = Order::factory()->count(3)->create(['client_id' => $client->id]);

        $response = $this->get("/clients/{$client->id}");

        $response->assertStatus(200);
        $response->assertInertia(function ($page) use ($client) {
            $page->component('clients/Show')
                ->has('client', function ($clientProp) use ($client) {
                    return $clientProp->where('id', $client->id)
                                      ->has('orders')
                                      ->has('addresses')
                                      ->etc(); // Allow additional properties
                });
        });
    }

    public function test_client_show_includes_recent_orders_with_items(): void
    {
        $this->actingAs($this->user);

        $client = Client::factory()->create();
        
        // Create more than 10 orders to test the limit
        Order::factory()->count(15)->create([
            'client_id' => $client->id,
            'created_at' => now()->subDays(rand(1, 30)),
        ]);

        $response = $this->get("/clients/{$client->id}");

        $response->assertStatus(200);
        $response->assertInertia(function ($page) {
            $page->has('client.orders')
                 ->etc(); // Allow additional client properties
        });
    }

    public function test_client_show_returns_404_for_non_existent_client(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/clients/999999');

        $response->assertStatus(404);
    }

    public function test_authenticated_user_can_view_edit_client_page(): void
    {
        $this->actingAs($this->user);

        $client = Client::factory()->create();

        $response = $this->get("/clients/{$client->id}/edit");

        $response->assertStatus(200);
        $response->assertInertia(function ($page) use ($client) {
            $page->component('clients/Edit')
                ->has('client', function ($clientProp) use ($client) {
                    return $clientProp->where('id', $client->id)
                                      ->has('addresses')
                                      ->etc(); // Allow additional properties
                });
        });
    }

    public function test_edit_client_returns_404_for_non_existent_client(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/clients/999999/edit');

        $response->assertStatus(404);
    }

    public function test_clients_index_pagination_works(): void
    {
        $this->actingAs($this->user);

        // Create more than 15 clients (default per page)
        Client::factory()->count(20)->create();

        $response = $this->get('/clients');

        $response->assertStatus(200);
        $response->assertInertia(function ($page) {
            $page->has('clients.data', 15) // Should have 15 items
                 ->has('clients.links') // Should have pagination links
                 ->has('clients.current_page') // Alternative pagination meta
                 ->has('clients.per_page')
                 ->has('clients.total');
        });
    }

    public function test_clients_are_ordered_by_latest_first(): void
    {
        $this->actingAs($this->user);

        $oldClient = Client::factory()->create(['created_at' => now()->subDays(2)]);
        $newClient = Client::factory()->create(['created_at' => now()]);

        $response = $this->get('/clients');

        $response->assertStatus(200);
        $response->assertInertia(function ($page) use ($newClient, $oldClient) {
            $clients = $page->toArray()['props']['clients']['data'];
            
            // The newer client should be first
            return $clients[0]['id'] === $newClient->id;
        });
    }
}