<?php

namespace Tests\Unit\Api;

use App\Models\ApiClient;
use App\Models\Order;
use App\Models\Client;
use App\Models\Address;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class OrderManagementTest extends TestCase
{
    use RefreshDatabase;

    private ApiClient $apiClient;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->apiClient = ApiClient::factory()->create();
        Queue::fake();
    }

    public function test_can_create_order_with_client_and_address(): void
    {
        $payload = [
            'client' => [
                'external_id' => 'CLIENT_001',
                'email' => 'test@example.com',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'phone' => '+420123456789',
            ],
            'shipping_address' => [
                'name' => 'John Doe',
                'street1' => '123 Main St',
                'city' => 'Prague',
                'postal_code' => '10000',
                'country_code' => 'CZ',
            ],
            'items' => [
                [
                    'sku' => 'ITEM-001',
                    'name' => 'Test Product',
                    'qty' => 2,
                    'price' => 29.99,
                    'tax_rate' => 0.21,
                ],
            ],
            'carrier' => 'balikovna',
            'currency' => 'EUR',
        ];

        $response = $this->withHmacAuth()
            ->postJson('/api/v1/orders', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'number',
                    'status',
                    'total_amount',
                    'currency',
                    'carrier',
                    'client',
                    'items',
                    'shipping_address',
                ],
                'message',
            ]);

        $this->assertDatabaseHas('orders', [
            'status' => Order::STATUS_NEW,
            'carrier' => 'balikovna',
            'currency' => 'EUR',
        ]);

        $this->assertDatabaseHas('clients', [
            'email' => 'test@example.com',
            'external_id' => 'CLIENT_001',
        ]);

        $this->assertDatabaseHas('order_items', [
            'sku' => 'ITEM-001',
            'qty' => 2,
            'price' => 29.99,
        ]);
    }

    public function test_can_list_orders_with_filters(): void
    {
        $client = Client::factory()->create();
        Order::factory()->count(3)->create(['client_id' => $client->id, 'status' => Order::STATUS_NEW]);
        Order::factory()->count(2)->create(['client_id' => $client->id, 'status' => Order::STATUS_PAID]);

        $response = $this->withHmacAuth()
            ->getJson('/api/v1/orders?status=new&client_id=' . $client->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'number',
                        'status',
                        'client',
                        'items',
                    ],
                ],
                'pagination',
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    public function test_can_show_order_by_different_identifiers(): void
    {
        $order = Order::factory()->create(['pmi_id' => 'PMI_12345']);

        // Test by ID
        $response = $this->withHmacAuth()
            ->getJson("/api/v1/orders/{$order->id}");
        $response->assertStatus(200);

        // Test by number
        $response = $this->withHmacAuth()
            ->getJson("/api/v1/orders/{$order->number}");
        $response->assertStatus(200);

        // Test by PMI ID
        $response = $this->withHmacAuth()
            ->getJson("/api/v1/orders/PMI_12345");
        $response->assertStatus(200)
            ->assertJsonPath('data.pmi_id', 'PMI_12345');
    }

    public function test_can_transition_order_status(): void
    {
        $order = Order::factory()->create(['status' => Order::STATUS_NEW]);

        $response = $this->withHmacAuth()
            ->postJson("/api/v1/orders/{$order->id}/transition", [
                'status' => Order::STATUS_CONFIRMED,
                'reason' => 'Manual confirmation',
                'metadata' => ['admin_user' => 'test@admin.com'],
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', Order::STATUS_CONFIRMED);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => Order::STATUS_CONFIRMED,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'order_id' => $order->id,
            'action' => 'transition',
            'reason' => 'Manual confirmation',
        ]);
    }

    public function test_cannot_transition_to_invalid_status(): void
    {
        $order = Order::factory()->create(['status' => Order::STATUS_NEW]);

        $response = $this->withHmacAuth()
            ->postJson("/api/v1/orders/{$order->id}/transition", [
                'status' => Order::STATUS_FULFILLED, // Cannot go directly from new to fulfilled
            ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['error', 'message']);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => Order::STATUS_NEW,
        ]);
    }

    public function test_can_update_order_carrier(): void
    {
        $order = Order::factory()->create(['carrier' => null]);

        $response = $this->withHmacAuth()
            ->patchJson("/api/v1/orders/{$order->id}", [
                'carrier' => 'dpd',
                'meta' => ['shipping_notes' => 'Handle with care'],
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.carrier', 'dpd');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'carrier' => 'dpd',
        ]);
    }

    public function test_can_delete_new_order_only(): void
    {
        $newOrder = Order::factory()->create(['status' => Order::STATUS_NEW]);
        $confirmedOrder = Order::factory()->create(['status' => Order::STATUS_CONFIRMED]);

        // Can delete new order
        $response = $this->withHmacAuth()
            ->deleteJson("/api/v1/orders/{$newOrder->id}");
        $response->assertStatus(200);
        $this->assertDatabaseMissing('orders', ['id' => $newOrder->id]);

        // Cannot delete confirmed order
        $response = $this->withHmacAuth()
            ->deleteJson("/api/v1/orders/{$confirmedOrder->id}");
        $response->assertStatus(422);
        $this->assertDatabaseHas('orders', ['id' => $confirmedOrder->id]);
    }

    public function test_requires_hmac_authentication(): void
    {
        $response = $this->postJson('/api/v1/orders', []);
        $response->assertStatus(401);
    }

    public function test_validates_order_creation_data(): void
    {
        $response = $this->withHmacAuth()
            ->postJson('/api/v1/orders', [
                'client' => ['email' => 'invalid-email'],
                'items' => [],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['client.email', 'client.first_name', 'items', 'shipping_address']);
    }

    protected function withHmacAuth(): self
    {
        $timestamp = time();
        $method = 'POST';
        $path = '/api/v1/orders';
        $body = '';
        
        // Create HMAC signature
        $stringToSign = $method . $path . $timestamp . hash('sha256', $body);
        $signature = base64_encode(hash_hmac('sha256', $stringToSign, $this->apiClient->secret, true));

        return $this->withHeaders([
            'X-Key-Id' => $this->apiClient->key_id,
            'X-Signature' => $signature,
            'X-Timestamp' => $timestamp,
            'Digest' => 'SHA-256=' . base64_encode(hash('sha256', $body, true)),
            'Content-Type' => 'application/json',
        ]);
    }
}
