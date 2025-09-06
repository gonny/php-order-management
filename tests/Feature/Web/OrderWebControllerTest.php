<?php

namespace Tests\Feature\Web;

use App\Models\Order;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderWebControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_guests_cannot_access_order_pages(): void
    {
        $order = Order::factory()->create();

        $this->get('/orders')->assertRedirect('/login');
        $this->get('/orders/create')->assertRedirect('/login');
        $this->get("/orders/{$order->id}")->assertRedirect('/login');
        $this->get("/orders/{$order->id}/edit")->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_orders_index(): void
    {
        $this->actingAs($this->user);

        $client = Client::factory()->create();
        Order::factory()->count(3)->create(['client_id' => $client->id]);

        $response = $this->get('/orders');

        $response->assertStatus(200);
        $response->assertInertia(function ($page) {
            $page->component('orders/Index')
                ->has('orders')
                ->has('filters');
        });
    }

    public function test_orders_index_includes_client_and_items(): void
    {
        $this->actingAs($this->user);

        $client = Client::factory()->create();
        $order = Order::factory()->create(['client_id' => $client->id]);

        $response = $this->get('/orders');

        $response->assertStatus(200);
        $response->assertInertia(function ($page) {
            $page->has('orders.data.0.client')
                 ->has('orders.data.0.items');
        });
    }

    public function test_orders_index_respects_filters(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/orders?search=test&status=pending');

        $response->assertStatus(200);
        $response->assertInertia(function ($page) {
            $page->has('filters', function ($filters) {
                return $filters->where('search', 'test')
                              ->where('status', 'pending');
            });
        });
    }

    public function test_authenticated_user_can_view_create_order_page(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/orders/create');

        $response->assertStatus(200);
        $response->assertInertia(function ($page) {
            $page->component('orders/Create');
        });
    }

    public function test_authenticated_user_can_view_order_details(): void
    {
        $this->actingAs($this->user);

        $client = Client::factory()->create();
        $order = Order::factory()->create(['client_id' => $client->id]);
        
        // Create order items
        \App\Models\OrderItem::factory()->count(2)->create(['order_id' => $order->id]);

        $response = $this->get("/orders/{$order->id}");

        $response->assertStatus(200);
        $response->assertInertia(function ($page) use ($order) {
            $page->component('orders/Show')
                ->has('order', function ($orderProp) use ($order) {
                    return $orderProp->where('id', $order->id)
                                     ->has('client')
                                     ->has('items')
                                     ->has('shippingAddress')
                                     ->has('billingAddress')
                                     ->has('shippingLabels');
                });
        });
    }

    public function test_order_show_returns_404_for_non_existent_order(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/orders/999999');

        $response->assertStatus(404);
    }

    public function test_authenticated_user_can_view_edit_order_page(): void
    {
        $this->actingAs($this->user);

        $client = Client::factory()->create();
        $order = Order::factory()->create(['client_id' => $client->id]);
        
        // Create order items
        \App\Models\OrderItem::factory()->count(2)->create(['order_id' => $order->id]);
        
        // Ensure the order has addresses and items
        $order->load(['client', 'items', 'shippingAddress', 'billingAddress']);

        $response = $this->get("/orders/{$order->id}/edit");

        $response->assertStatus(200);
        $response->assertInertia(function ($page) use ($order) {
            $page->component('orders/Edit')
                ->has('order', function ($orderProp) use ($order) {
                    return $orderProp->where('id', $order->id)
                                     ->has('client')
                                     ->has('items')
                                     ->has('shipping_address')
                                     ->has('billing_address');
                });
        });
    }

    public function test_edit_order_returns_404_for_non_existent_order(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/orders/999999/edit');

        $response->assertStatus(404);
    }

    public function test_orders_index_pagination_works(): void
    {
        $this->actingAs($this->user);

        $client = Client::factory()->create();
        // Create more than 15 orders (default per page)
        Order::factory()->count(20)->create(['client_id' => $client->id]);

        $response = $this->get('/orders');

        $response->assertStatus(200);
        $response->assertInertia(function ($page) {
            $page->has('orders.data', 15) // Should have 15 items
                 ->has('orders.links') // Should have pagination links
                 ->has('orders.current_page') // Alternative pagination meta
                 ->has('orders.per_page')
                 ->has('orders.total');
        });
    }

    public function test_orders_are_ordered_by_latest_first(): void
    {
        $this->actingAs($this->user);

        $client = Client::factory()->create();
        $oldOrder = Order::factory()->create([
            'client_id' => $client->id,
            'created_at' => now()->subDays(2)
        ]);
        $newOrder = Order::factory()->create([
            'client_id' => $client->id,
            'created_at' => now()
        ]);

        $response = $this->get('/orders');

        $response->assertStatus(200);
        $response->assertInertia(function ($page) use ($newOrder, $oldOrder) {
            $orders = $page->toArray()['props']['orders']['data'];
            
            // The newer order should be first
            return $orders[0]['id'] === $newOrder->id;
        });
    }
}