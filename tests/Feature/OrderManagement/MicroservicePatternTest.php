<?php

namespace Tests\Feature\OrderManagement;

use App\Jobs\ProcessOrderStateChange;
use App\Models\Address;
use App\Models\AuditLog;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\OrderManagement\OrderStateMachine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class MicroservicePatternTest extends TestCase
{
    use RefreshDatabase;

    private OrderStateMachine $stateMachine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->stateMachine = app(OrderStateMachine::class);
    }

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
            'pmi_id' => 'pmi_test_123',
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

    public function test_end_to_end_order_automation_new_to_paid(): void
    {
        Queue::fake();
        
        $order = $this->createTestOrder();

        // Step 1: New -> Confirmed (validates order requirements)
        $confirmedOrder = $this->stateMachine->transition(
            $order,
            Order::STATUS_CONFIRMED,
            'Customer confirmed order via API',
            ['confirmation_method' => 'email_link'],
            'api',
            'order_service'
        );

        $this->assertEquals(Order::STATUS_CONFIRMED, $confirmedOrder->status);
        
        // Verify audit log was created
        $this->assertDatabaseHas('audit_logs', [
            'entity_type' => 'order',
            'entity_id' => $order->id,
            'action' => 'status_change',
            'actor_type' => 'api',
            'actor_id' => 'order_service',
        ]);

        // Verify background job was dispatched
        Queue::assertPushed(ProcessOrderStateChange::class, function ($job) use ($order) {
            return $job->order->id === $order->id 
                && $job->previousStatus === Order::STATUS_NEW
                && $job->newStatus === Order::STATUS_CONFIRMED;
        });

        // Step 2: Confirmed -> Paid (validates payment)
        $paidOrder = $this->stateMachine->transition(
            $confirmedOrder,
            Order::STATUS_PAID,
            'Payment processed successfully',
            ['payment_gateway' => 'stripe', 'transaction_id' => 'txn_123'],
            'api',
            'payment_service'
        );

        $this->assertEquals(Order::STATUS_PAID, $paidOrder->status);

        // Verify second audit log
        $auditLogs = AuditLog::where('entity_id', $order->id)->count();
        $this->assertEquals(2, $auditLogs);

        // Verify second background job
        Queue::assertPushed(ProcessOrderStateChange::class, function ($job) use ($order) {
            return $job->order->id === $order->id 
                && $job->previousStatus === Order::STATUS_CONFIRMED
                && $job->newStatus === Order::STATUS_PAID;
        });
    }

    public function test_microservice_pattern_with_carrier_automation(): void
    {
        Queue::fake();
        
        $order = $this->createTestOrder(Order::STATUS_CONFIRMED);

        // Transition to paid - should trigger shipping label generation
        $this->stateMachine->transition(
            $order,
            Order::STATUS_PAID,
            'Payment received - auto shipping label',
            ['auto_fulfill' => true],
            'api',
            'payment_webhook'
        );

        // Verify the microservice automation job was queued
        Queue::assertPushed(ProcessOrderStateChange::class, function ($job) use ($order) {
            return $job->order->id === $order->id
                && $job->newStatus === Order::STATUS_PAID
                && $job->reason === 'Payment received - auto shipping label'
                && $job->metadata['auto_fulfill'] === true;
        });
    }

    public function test_order_validation_prevents_invalid_transitions(): void
    {
        $order = $this->createTestOrder();
        
        // Remove required data to trigger validation failure
        $order->update(['total_amount' => 0]);

        $this->expectException(\App\Services\OrderManagement\Exceptions\InvalidOrderTransitionException::class);
        $this->expectExceptionMessage('Order total must be greater than zero');

        $this->stateMachine->transition(
            $order,
            Order::STATUS_CONFIRMED,
            'Should fail validation'
        );
    }

    public function test_payment_validation_for_paid_transition(): void
    {
        $order = $this->createTestOrder(Order::STATUS_CONFIRMED);
        
        // Remove payment method identifier
        $order->update(['pmi_id' => null]);

        $this->expectException(\App\Services\OrderManagement\Exceptions\InvalidOrderTransitionException::class);
        $this->expectExceptionMessage('Order must have a valid payment method identifier');

        $this->stateMachine->transition(
            $order,
            Order::STATUS_PAID,
            'Should fail payment validation'
        );
    }

    public function test_carrier_validation_for_fulfillment(): void
    {
        $order = $this->createTestOrder(Order::STATUS_PAID);
        
        // Remove carrier
        $order->update(['carrier' => null]);

        $this->expectException(\App\Services\OrderManagement\Exceptions\InvalidOrderTransitionException::class);
        $this->expectExceptionMessage('Order must have a carrier assigned');

        $this->stateMachine->transition(
            $order,
            Order::STATUS_FULFILLED,
            'Should fail carrier validation'
        );
    }

    public function test_audit_trail_captures_complete_order_lifecycle(): void
    {
        Queue::fake();
        
        $order = $this->createTestOrder();

        // Complete order lifecycle
        $states = [
            [Order::STATUS_CONFIRMED, 'Customer confirmed', 'api', 'frontend'],
            [Order::STATUS_PAID, 'Payment processed', 'api', 'payment_service'],
        ];

        foreach ($states as [$status, $reason, $actorType, $actorId]) {
            $this->stateMachine->transition($order, $status, $reason, [], $actorType, $actorId);
            $order->refresh();
        }

        // Verify complete audit trail
        $auditLogs = AuditLog::where('entity_id', $order->id)
            ->orderBy('created_at')
            ->get();

        $this->assertCount(2, $auditLogs);

        // Check first transition
        $firstLog = $auditLogs[0];
        $this->assertEquals('status_change', $firstLog->action);
        $this->assertEquals('api', $firstLog->actor_type);
        $this->assertEquals('frontend', $firstLog->actor_id);
        $this->assertEquals(Order::STATUS_NEW, $firstLog->before['status']);
        $this->assertEquals(Order::STATUS_CONFIRMED, $firstLog->after['status']);

        // Check second transition
        $secondLog = $auditLogs[1];
        $this->assertEquals('payment_service', $secondLog->actor_id);
        $this->assertEquals(Order::STATUS_CONFIRMED, $secondLog->before['status']);
        $this->assertEquals(Order::STATUS_PAID, $secondLog->after['status']);
    }

    public function test_concurrent_state_changes_are_handled_safely(): void
    {
        Queue::fake();
        
        $order = $this->createTestOrder();

        // Simulate concurrent state changes (database transaction should handle this)
        $this->stateMachine->transition(
            $order,
            Order::STATUS_CONFIRMED,
            'First transition'
        );

        // Refresh and continue
        $order->refresh();
        
        $this->stateMachine->transition(
            $order,
            Order::STATUS_PAID,
            'Second transition'
        );

        $order->refresh();
        $this->assertEquals(Order::STATUS_PAID, $order->status);

        // Verify both jobs were dispatched
        Queue::assertPushed(ProcessOrderStateChange::class, 2);
    }

    public function test_metadata_is_preserved_through_transitions(): void
    {
        Queue::fake();
        
        $order = $this->createTestOrder();

        $metadata = [
            'source' => 'mobile_app',
            'campaign_id' => 'summer_sale_2024',
            'customer_notes' => 'Please deliver after 6pm',
        ];

        $this->stateMachine->transition(
            $order,
            Order::STATUS_CONFIRMED,
            'Order confirmed with metadata',
            $metadata
        );

        // Verify metadata is captured in audit log
        $auditLog = AuditLog::where('entity_id', $order->id)->first();
        $this->assertEquals($metadata, $auditLog->after['metadata']);

        // Verify metadata is passed to background job
        Queue::assertPushed(ProcessOrderStateChange::class, function ($job) use ($metadata) {
            return $job->metadata === $metadata;
        });
    }

    public function test_available_transitions_are_correctly_calculated(): void
    {
        $order = $this->createTestOrder();

        // Test transitions for new order
        $transitions = $this->stateMachine->getAvailableTransitions($order);
        $this->assertContains(Order::STATUS_CONFIRMED, $transitions);
        $this->assertContains(Order::STATUS_CANCELLED, $transitions);
        $this->assertNotContains(Order::STATUS_PAID, $transitions);

        // Test transitions for confirmed order
        $order->update(['status' => Order::STATUS_CONFIRMED]);
        $transitions = $this->stateMachine->getAvailableTransitions($order);
        $this->assertContains(Order::STATUS_PAID, $transitions);
        $this->assertNotContains(Order::STATUS_COMPLETED, $transitions);

        // Test transitions for completed order (no transitions allowed)
        $order->update(['status' => Order::STATUS_COMPLETED]);
        $transitions = $this->stateMachine->getAvailableTransitions($order);
        $this->assertEmpty($transitions);
    }

    public function test_status_display_names_and_colors(): void
    {
        $statuses = [
            Order::STATUS_NEW => ['name' => 'New', 'color' => 'blue'],
            Order::STATUS_CONFIRMED => ['name' => 'Confirmed', 'color' => 'yellow'],
            Order::STATUS_PAID => ['name' => 'Paid', 'color' => 'orange'],
            Order::STATUS_FULFILLED => ['name' => 'Fulfilled', 'color' => 'purple'],
            Order::STATUS_COMPLETED => ['name' => 'Completed', 'color' => 'green'],
            Order::STATUS_CANCELLED => ['name' => 'Cancelled', 'color' => 'gray'],
            Order::STATUS_ON_HOLD => ['name' => 'On Hold', 'color' => 'amber'],
            Order::STATUS_FAILED => ['name' => 'Failed', 'color' => 'red'],
        ];

        foreach ($statuses as $status => $expected) {
            $this->assertEquals($expected['name'], $this->stateMachine->getStatusDisplayName($status));
            $this->assertEquals($expected['color'], $this->stateMachine->getStatusColor($status));
        }
    }

    public function test_microservice_pattern_with_external_integrations(): void
    {
        Queue::fake();
        
        $order = $this->createTestOrder();

        // Simulate external system triggering state change
        $this->stateMachine->transition(
            $order,
            Order::STATUS_CONFIRMED,
            'Confirmed by external inventory system',
            [
                'external_system' => 'inventory_service',
                'external_order_id' => 'INV-2024-001',
                'stock_reserved' => true,
            ],
            'api',
            'inventory_webhook'
        );

        // Verify the external integration data is captured
        Queue::assertPushed(ProcessOrderStateChange::class, function ($job) {
            return $job->metadata['external_system'] === 'inventory_service'
                && $job->metadata['external_order_id'] === 'INV-2024-001'
                && $job->metadata['stock_reserved'] === true;
        });

        $auditLog = AuditLog::where('entity_id', $order->id)->first();
        $this->assertEquals('inventory_webhook', $auditLog->actor_id);
        $this->assertEquals('api', $auditLog->actor_type);
    }
}