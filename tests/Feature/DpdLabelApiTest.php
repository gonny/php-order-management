<?php

namespace Tests\Feature;

use App\Jobs\GenerateDpdLabelJob;
use App\Jobs\DeleteDpdShipmentJob;
use App\Models\Address;
use App\Models\ApiClient;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class DpdLabelApiTest extends TestCase
{
    use RefreshDatabase;

    private ApiClient $apiClient;
    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create API client for authentication
        $this->apiClient = ApiClient::create([
            'name' => 'Test Client',
            'key_id' => 'test-key',
            'secret_hash' => hash('sha256', 'test-secret'),
            'active' => true,
        ]);

        // Create test order with required relationships
        $client = Client::create([
            'external_id' => 'EXT-123',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
        ]);

        $shippingAddress = Address::create([
            'type' => 'shipping',
            'client_id' => $client->id,
            'name' => 'John Doe',
            'street1' => '123 Main St',
            'city' => 'Prague',
            'postal_code' => '12000',
            'country_code' => 'CZ',
        ]);

        $this->order = Order::create([
            'number' => 'ORD-TEST-001',
            'client_id' => $client->id,
            'status' => Order::STATUS_PAID,
            'total_amount' => 100.00,
            'currency' => 'CZK',
            'shipping_address_id' => $shippingAddress->id,
        ]);

        // Add some items
        OrderItem::create([
            'order_id' => $this->order->id,
            'sku' => 'ITEM-001',
            'name' => 'Test Item',
            'qty' => 2,
            'price' => 50.00,
        ]);
    }

    public function test_can_generate_dpd_home_delivery_label()
    {
        Queue::fake();

        $response = $this->withHmacAuth()
            ->postJson("/api/v1/orders/{$this->order->id}/label/dpd", [
                'shipping_method' => 'DPD_Home',
            ]);

        $response->assertStatus(202)
            ->assertJsonStructure([
                'message',
                'order_id',
                'status',
                'shipping_method'
            ])
            ->assertJson([
                'status' => 'queued',
                'shipping_method' => 'DPD_Home',
                'order_id' => $this->order->id,
            ]);

        Queue::assertPushed(GenerateDpdLabelJob::class, function ($job) {
            return $job->order->id === $this->order->id;
        });

        // Check order was updated
        $this->order->refresh();
        $this->assertEquals(Order::CARRIER_DPD, $this->order->carrier);
        $this->assertEquals('DPD_Home', $this->order->shipping_method);
        $this->assertNull($this->order->pickup_point_id);
    }

    public function test_can_generate_dpd_pickup_point_label()
    {
        Queue::fake();

        $response = $this->withHmacAuth()
            ->postJson("/api/v1/orders/{$this->order->id}/label/dpd", [
                'shipping_method' => 'DPD_PickupPoint',
                'pickup_point_id' => 'PP123456',
            ]);

        $response->assertStatus(202)
            ->assertJsonStructure([
                'message',
                'order_id',
                'status',
                'shipping_method'
            ])
            ->assertJson([
                'status' => 'queued',
                'shipping_method' => 'DPD_PickupPoint',
            ]);

        Queue::assertPushed(GenerateDpdLabelJob::class);

        // Check order was updated
        $this->order->refresh();
        $this->assertEquals('DPD_PickupPoint', $this->order->shipping_method);
        $this->assertEquals('PP123456', $this->order->pickup_point_id);
    }

    public function test_validates_required_pickup_point_id_for_pickup_delivery()
    {
        $response = $this->withHmacAuth()
            ->postJson("/api/v1/orders/{$this->order->id}/label/dpd", [
                'shipping_method' => 'DPD_PickupPoint',
                // Missing pickup_point_id
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['pickup_point_id']);
    }

    public function test_validates_shipping_method()
    {
        $response = $this->withHmacAuth()
            ->postJson("/api/v1/orders/{$this->order->id}/label/dpd", [
                'shipping_method' => 'Invalid_Method',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['shipping_method']);
    }

    public function test_rejects_unpaid_orders()
    {
        $this->order->update(['status' => Order::STATUS_NEW]);

        $response = $this->withHmacAuth()
            ->postJson("/api/v1/orders/{$this->order->id}/label/dpd", [
                'shipping_method' => 'DPD_Home',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'error' => 'Cannot generate DPD label',
                'message' => 'Order must be paid before generating shipping label',
            ]);
    }

    public function test_rejects_orders_without_shipping_address()
    {
        $this->order->update(['shipping_address_id' => null]);

        $response = $this->withHmacAuth()
            ->postJson("/api/v1/orders/{$this->order->id}/label/dpd", [
                'shipping_method' => 'DPD_Home',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'error' => 'Cannot generate DPD label',
                'message' => 'Order must have a shipping address',
            ]);
    }

    public function test_rejects_orders_outside_cz_sk()
    {
        $this->order->shippingAddress->update(['country_code' => 'DE']);

        $response = $this->withHmacAuth()
            ->postJson("/api/v1/orders/{$this->order->id}/label/dpd", [
                'shipping_method' => 'DPD_Home',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'error' => 'Cannot generate DPD label',
                'message' => 'DPD shipping only available for CZ and SK',
            ]);
    }

    public function test_rejects_duplicate_dpd_shipment()
    {
        $this->order->update(['dpd_shipment_id' => 'EXISTING123']);

        $response = $this->withHmacAuth()
            ->postJson("/api/v1/orders/{$this->order->id}/label/dpd", [
                'shipping_method' => 'DPD_Home',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'error' => 'DPD label already exists',
                'message' => 'Order already has a DPD shipment',
            ]);
    }

    public function test_can_delete_dpd_shipment()
    {
        Queue::fake();
        
        $this->order->update(['dpd_shipment_id' => 'SHIP123456']);

        $response = $this->withHmacAuth()
            ->deleteJson("/api/v1/orders/{$this->order->id}/shipment/dpd");

        $response->assertStatus(202)
            ->assertJsonStructure([
                'message',
                'order_id',
                'shipment_id',
                'status'
            ])
            ->assertJson([
                'status' => 'queued',
                'shipment_id' => 'SHIP123456',
            ]);

        Queue::assertPushed(DeleteDpdShipmentJob::class, function ($job) {
            return $job->order->id === $this->order->id;
        });
    }

    public function test_rejects_delete_when_no_shipment_exists()
    {
        $response = $this->withHmacAuth()
            ->deleteJson("/api/v1/orders/{$this->order->id}/shipment/dpd");

        $response->assertStatus(422)
            ->assertJson([
                'error' => 'No DPD shipment to delete',
                'message' => 'Order does not have a DPD shipment',
            ]);
    }

    /**
     * Helper method to add HMAC authentication headers
     */
    private function withHmacAuth()
    {
        $timestamp = now()->timestamp;
        $body = '';
        $path = '';
        
        // Simple HMAC for testing - in real implementation this would be more complex
        $signature = hash_hmac('sha256', "GET{$path}{$body}{$timestamp}", 'test-secret');
        
        return $this->withHeaders([
            'X-API-Key' => $this->apiClient->key_id,
            'X-Timestamp' => $timestamp,
            'X-Signature' => $signature,
        ]);
    }
}
