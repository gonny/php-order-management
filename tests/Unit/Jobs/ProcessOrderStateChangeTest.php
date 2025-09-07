<?php

namespace Tests\Unit\Jobs;

use App\Jobs\GenerateShippingLabel;
use App\Jobs\ProcessOrderStateChange;
use App\Models\Address;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ShippingLabel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ProcessOrderStateChangeTest extends TestCase
{
    use RefreshDatabase;

    private function createTestOrder(string $status = Order::STATUS_NEW): Order
    {
        $client = Client::factory()->create();
        $address = Address::factory()->create();

        $order = Order::factory()->create([
            'client_id' => $client->id,
            'shipping_address_id' => $address->id,
            'billing_address_id' => $address->id,
            'status' => $status,
            'carrier' => 'dpd',
            'total_amount' => 100.00,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'sku' => 'TEST-SKU-001',
            'name' => 'Test Product',
            'qty' => 1,
            'price' => 100.00,
        ]);

        return $order;
    }

    public function test_job_handles_basic_state_change(): void
    {
        $order = $this->createTestOrder();

        $job = new ProcessOrderStateChange(
            $order,
            Order::STATUS_NEW,
            Order::STATUS_CONFIRMED,
            'Order confirmed by customer',
            ['payment_method' => 'credit_card']
        );

        // Expect log entry
        Log::shouldReceive('info')
            ->with('Processing order state change', [
                'order_id' => $order->id,
                'previous_status' => Order::STATUS_NEW,
                'new_status' => Order::STATUS_CONFIRMED,
                'reason' => 'Order confirmed by customer',
            ])
            ->once();

        Log::shouldReceive('info')
            ->with('Customer notification sent', \Mockery::any())
            ->once();

        Log::shouldReceive('info')
            ->with('Order state change processed successfully', [
                'order_id' => $order->id,
                'new_status' => Order::STATUS_CONFIRMED,
            ])
            ->once();

        Log::shouldReceive('info')
            ->with('External webhook data prepared', \Mockery::any())
            ->once();

        $job->handle();

        $this->assertTrue(true); // Job completed without exception
    }

    public function test_paid_order_triggers_shipping_label_generation(): void
    {
        Queue::fake();

        $order = $this->createTestOrder(Order::STATUS_CONFIRMED);

        $job = new ProcessOrderStateChange(
            $order,
            Order::STATUS_CONFIRMED,
            Order::STATUS_PAID,
            'Payment received',
            ['payment_id' => 'pay_123']
        );

        Log::shouldReceive('info')->withAnyArgs();

        $job->handle();

        // Assert that shipping label generation was queued
        Queue::assertPushed(GenerateShippingLabel::class, function ($job) use ($order) {
            return $job->order->id === $order->id;
        });
    }

    public function test_fulfilled_order_updates_estimated_delivery(): void
    {
        $order = $this->createTestOrder(Order::STATUS_PAID);

        // Create a shipping label with estimated delivery
        ShippingLabel::factory()->create([
            'order_id' => $order->id,
            'status' => 'generated',
            'meta' => [
                'estimated_delivery' => '2024-01-15T10:00:00Z',
                'tracking_number' => 'TRK123456',
            ],
        ]);

        $job = new ProcessOrderStateChange(
            $order,
            Order::STATUS_PAID,
            Order::STATUS_FULFILLED,
            'Order fulfilled and shipped',
            []
        );

        Log::shouldReceive('info')->withAnyArgs();

        $job->handle();

        // Check that estimated delivery was added to order meta
        $order->refresh();
        $this->assertArrayHasKey('estimated_delivery', $order->meta ?? []);
        $this->assertEquals('2024-01-15T10:00:00Z', $order->meta['estimated_delivery']);
    }

    public function test_completed_order_marks_delivery_time(): void
    {
        $order = $this->createTestOrder(Order::STATUS_FULFILLED);

        $job = new ProcessOrderStateChange(
            $order,
            Order::STATUS_FULFILLED,
            Order::STATUS_COMPLETED,
            'Order delivered',
            []
        );

        Log::shouldReceive('info')->withAnyArgs();

        $job->handle();

        // Check that delivered_at was added to order meta
        $order->refresh();
        $this->assertArrayHasKey('delivered_at', $order->meta ?? []);
        $this->assertNotNull($order->meta['delivered_at']);
    }

    public function test_cancelled_order_voids_shipping_labels(): void
    {
        $order = $this->createTestOrder(Order::STATUS_PAID);

        // Create active shipping labels
        ShippingLabel::factory()->create([
            'order_id' => $order->id,
            'status' => 'generated',
        ]);

        ShippingLabel::factory()->create([
            'order_id' => $order->id,
            'status' => 'generated',
        ]);

        $job = new ProcessOrderStateChange(
            $order,
            Order::STATUS_PAID,
            Order::STATUS_CANCELLED,
            'Customer requested cancellation',
            []
        );

        Log::shouldReceive('info')->withAnyArgs();

        $job->handle();

        // Check that shipping labels were voided
        $this->assertEquals(2, $order->shippingLabels()->where('status', 'voided')->count());
    }

    public function test_failed_order_logs_failure_reason(): void
    {
        $order = $this->createTestOrder(Order::STATUS_PAID);

        $job = new ProcessOrderStateChange(
            $order,
            Order::STATUS_PAID,
            Order::STATUS_FAILED,
            'Payment processing failed',
            ['error_code' => 'CARD_DECLINED']
        );

        Log::shouldReceive('info')->withAnyArgs();

        $job->handle();

        // Check that failure reason was logged in order meta
        $order->refresh();
        $this->assertArrayHasKey('failure_reason', $order->meta ?? []);
        $this->assertEquals('Payment processing failed', $order->meta['failure_reason']);
        $this->assertArrayHasKey('failed_at', $order->meta ?? []);
    }

    public function test_customer_notifications_sent_for_appropriate_statuses(): void
    {
        $order = $this->createTestOrder();

        $job = new ProcessOrderStateChange(
            $order,
            Order::STATUS_NEW,
            Order::STATUS_CONFIRMED,
            'Status changed to confirmed',
            []
        );

        Log::shouldReceive('info')
            ->with('Processing order state change', \Mockery::any())
            ->once();

        Log::shouldReceive('info')
            ->with('Customer notification sent', [
                'order_id' => $order->id,
                'customer_email' => $order->client->email,
                'status' => Order::STATUS_CONFIRMED,
            ])
            ->once();

        Log::shouldReceive('info')
            ->with('Order state change processed successfully', \Mockery::any())
            ->once();

        Log::shouldReceive('info')
            ->with('External webhook data prepared', \Mockery::any())
            ->once();

        // Also expect potential error logs
        Log::shouldReceive('error')->withAnyArgs()->zeroOrMoreTimes();

        $job->handle();

        $this->assertTrue(true); // Job completed successfully
    }

    public function test_job_handles_exceptions_gracefully(): void
    {
        $order = $this->createTestOrder();

        // Force an exception by using invalid order state
        $order->status = 'invalid_status';

        $job = new ProcessOrderStateChange(
            $order,
            'invalid_status',
            Order::STATUS_CONFIRMED,
            'Test exception handling',
            []
        );

        Log::shouldReceive('info')
            ->with('Processing order state change', \Mockery::any())
            ->once();

        Log::shouldReceive('error')
            ->with('Failed to process order state change', \Mockery::any())
            ->once();

        $this->expectException(\Exception::class);

        $job->handle();
    }

    public function test_webhook_data_includes_all_required_fields(): void
    {
        $order = $this->createTestOrder();

        $job = new ProcessOrderStateChange(
            $order,
            Order::STATUS_NEW,
            Order::STATUS_CONFIRMED,
            'Test webhook data',
            ['custom_field' => 'test_value']
        );

        Log::shouldReceive('info')
            ->with('Processing order state change', \Mockery::any())
            ->once();

        Log::shouldReceive('info')
            ->with('Customer notification sent', \Mockery::any())
            ->once();

        Log::shouldReceive('info')
            ->with('Order state change processed successfully', \Mockery::any())
            ->once();

        Log::shouldReceive('info')
            ->with('External webhook data prepared', \Mockery::type('array'))
            ->once()
            ->andReturnUsing(function ($message, $data) use ($order) {
                // Verify webhook data structure
                $this->assertArrayHasKey('order_id', $data);
                $this->assertArrayHasKey('order_number', $data);
                $this->assertArrayHasKey('previous_status', $data);
                $this->assertArrayHasKey('new_status', $data);
                $this->assertArrayHasKey('reason', $data);
                $this->assertArrayHasKey('timestamp', $data);
                $this->assertArrayHasKey('metadata', $data);

                $this->assertEquals($order->id, $data['order_id']);
                $this->assertEquals($order->number, $data['order_number']);
                $this->assertEquals(Order::STATUS_NEW, $data['previous_status']);
                $this->assertEquals(Order::STATUS_CONFIRMED, $data['new_status']);
                $this->assertEquals('Test webhook data', $data['reason']);
                $this->assertEquals(['custom_field' => 'test_value'], $data['metadata']);

                return true;
            });

        $job->handle();
    }

    public function test_retry_mechanism_properties(): void
    {
        $order = $this->createTestOrder();

        $job = new ProcessOrderStateChange(
            $order,
            Order::STATUS_NEW,
            Order::STATUS_CONFIRMED,
            'Test retry properties',
            []
        );

        // Test job configuration
        $this->assertEquals(3, $job->tries);
        $this->assertEquals(60, $job->timeout);
    }

    public function test_job_without_carrier_skips_label_generation(): void
    {
        Queue::fake();

        $order = $this->createTestOrder(Order::STATUS_CONFIRMED);
        $order->update(['carrier' => null]); // Remove carrier

        $job = new ProcessOrderStateChange(
            $order,
            Order::STATUS_CONFIRMED,
            Order::STATUS_PAID,
            'Payment received',
            []
        );

        Log::shouldReceive('info')->withAnyArgs();

        $job->handle();

        // Assert that no shipping label generation was queued
        Queue::assertNotPushed(GenerateShippingLabel::class);
    }
}
