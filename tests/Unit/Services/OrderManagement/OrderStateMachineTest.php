<?php

namespace Tests\Unit\Services\OrderManagement;

use App\Models\Address;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ShippingLabel;
use App\Services\OrderManagement\AuditLogger;
use App\Services\OrderManagement\Exceptions\InvalidOrderTransitionException;
use App\Services\OrderManagement\OrderStateMachine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderStateMachineTest extends TestCase
{
    use RefreshDatabase;

    private OrderStateMachine $stateMachine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->stateMachine = new OrderStateMachine(new AuditLogger());
    }

    public function test_can_transition_from_new_to_confirmed(): void
    {
        $order = $this->createValidOrder();

        $this->assertTrue($this->stateMachine->canTransition($order, Order::STATUS_CONFIRMED));
    }

    public function test_cannot_transition_from_new_to_paid(): void
    {
        $order = $this->createValidOrder();

        $this->assertFalse($this->stateMachine->canTransition($order, Order::STATUS_PAID));
    }

    public function test_can_transition_order_from_new_to_confirmed(): void
    {
        $order = $this->createValidOrder();

        $transitionedOrder = $this->stateMachine->transition($order, Order::STATUS_CONFIRMED);

        $this->assertEquals(Order::STATUS_CONFIRMED, $transitionedOrder->status);
    }

    public function test_transition_throws_exception_for_invalid_transition(): void
    {
        $order = $this->createValidOrder();

        $this->expectException(InvalidOrderTransitionException::class);
        $this->stateMachine->transition($order, Order::STATUS_PAID);
    }

    public function test_transition_validates_order_confirmation_requirements(): void
    {
        $client = Client::factory()->create();
        $order = Order::factory()->create([
            'client_id' => $client->id,
            'status' => Order::STATUS_NEW,
            'total_amount' => 0, // Invalid total
            'shipping_address_id' => null,
            'billing_address_id' => null,
        ]);

        // The validation should check total_amount, but it checks items first
        $this->expectException(InvalidOrderTransitionException::class);
        $this->expectExceptionMessage('Order must have at least one item');
        
        $this->stateMachine->transition($order, Order::STATUS_CONFIRMED);
    }

    public function test_transition_validates_payment_requirements(): void
    {
        $order = $this->createValidOrder([
            'status' => Order::STATUS_CONFIRMED,
        ]);
        
        // Clear the pmi_id after creation
        $order->update(['pmi_id' => null]);

        $this->expectException(InvalidOrderTransitionException::class);
        $this->expectExceptionMessage('Order must have a valid payment method identifier');
        
        $this->stateMachine->transition($order, Order::STATUS_PAID);
    }

    public function test_transition_validates_fulfillment_requirements(): void
    {
        $order = $this->createValidOrder([
            'status' => Order::STATUS_PAID,
        ]);
        
        // Clear the carrier after creation
        $order->update(['carrier' => null]);

        $this->expectException(InvalidOrderTransitionException::class);
        $this->expectExceptionMessage('Order must have a carrier assigned');
        
        $this->stateMachine->transition($order, Order::STATUS_FULFILLED);
    }

    public function test_get_available_transitions_returns_correct_options(): void
    {
        $order = $this->createValidOrder();

        $transitions = $this->stateMachine->getAvailableTransitions($order);

        $this->assertContains(Order::STATUS_CONFIRMED, $transitions);
        $this->assertContains(Order::STATUS_CANCELLED, $transitions);
        $this->assertNotContains(Order::STATUS_PAID, $transitions);
    }

    public function test_get_status_display_name(): void
    {
        $this->assertEquals('New', $this->stateMachine->getStatusDisplayName(Order::STATUS_NEW));
        $this->assertEquals('Confirmed', $this->stateMachine->getStatusDisplayName(Order::STATUS_CONFIRMED));
        $this->assertEquals('Paid', $this->stateMachine->getStatusDisplayName(Order::STATUS_PAID));
    }

    public function test_get_status_color(): void
    {
        $this->assertEquals('blue', $this->stateMachine->getStatusColor(Order::STATUS_NEW));
        $this->assertEquals('green', $this->stateMachine->getStatusColor(Order::STATUS_COMPLETED));
        $this->assertEquals('red', $this->stateMachine->getStatusColor(Order::STATUS_FAILED));
    }

    public function test_completed_orders_cannot_transition_anywhere(): void
    {
        $order = $this->createValidOrder(['status' => Order::STATUS_COMPLETED]);

        $transitions = $this->stateMachine->getAvailableTransitions($order);

        $this->assertEmpty($transitions);
    }

    public function test_on_hold_orders_can_return_to_previous_states(): void
    {
        $order = $this->createValidOrder(['status' => Order::STATUS_ON_HOLD]);

        $transitions = $this->stateMachine->getAvailableTransitions($order);

        $this->assertContains(Order::STATUS_NEW, $transitions);
        $this->assertContains(Order::STATUS_CONFIRMED, $transitions);
        $this->assertContains(Order::STATUS_PAID, $transitions);
    }

    public function test_failed_orders_can_be_restarted(): void
    {
        $order = $this->createValidOrder(['status' => Order::STATUS_FAILED]);

        $transitions = $this->stateMachine->getAvailableTransitions($order);

        $this->assertContains(Order::STATUS_NEW, $transitions);
        $this->assertContains(Order::STATUS_ON_HOLD, $transitions);
    }

    public function test_transition_creates_audit_log(): void
    {
        $order = $this->createValidOrder();

        $this->stateMachine->transition($order, Order::STATUS_CONFIRMED);

        $this->assertDatabaseHas('audit_logs', [
            'entity_type' => 'order',
            'entity_id' => $order->id,
            'action' => 'status_change',
        ]);
    }

    private function createValidOrder(array $overrides = []): Order
    {
        $client = Client::factory()->create();
        $shippingAddress = Address::factory()->create([
            'client_id' => $client->id,
            'type' => 'shipping',
        ]);
        $billingAddress = Address::factory()->create([
            'client_id' => $client->id,
            'type' => 'billing',
        ]);

        $order = Order::factory()->create(array_merge([
            'client_id' => $client->id,
            'shipping_address_id' => $shippingAddress->id,
            'billing_address_id' => $billingAddress->id,
            'status' => Order::STATUS_NEW,
            'total_amount' => 100.00,
            'pmi_id' => 'pmi_123456789',
            'carrier' => Order::CARRIER_DPD,
        ], $overrides));

        // Add order items
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'price' => 50.00,
            'qty' => 2,
        ]);

        // Add shipping label if order is paid or higher
        if (in_array($order->status, [Order::STATUS_PAID, Order::STATUS_FULFILLED, Order::STATUS_COMPLETED])) {
            ShippingLabel::factory()->create([
                'order_id' => $order->id,
                'carrier' => $order->carrier,
                'status' => 'generated',
            ]);
        }

        return $order->fresh();
    }
}
