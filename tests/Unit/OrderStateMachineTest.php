<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\Client;
use App\Models\OrderItem;
use App\Models\Address;
use App\Services\OrderStateMachine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderStateMachineTest extends TestCase
{
    use RefreshDatabase;

    protected OrderStateMachine $stateMachine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->stateMachine = new OrderStateMachine();
    }

    public function test_can_check_valid_transitions()
    {
        $order = $this->createOrder('new');

        $this->assertTrue($this->stateMachine->canTransition($order, 'confirmed'));
        $this->assertTrue($this->stateMachine->canTransition($order, 'cancelled'));
        $this->assertTrue($this->stateMachine->canTransition($order, 'on_hold'));
        $this->assertFalse($this->stateMachine->canTransition($order, 'paid'));
        $this->assertFalse($this->stateMachine->canTransition($order, 'fulfilled'));
    }

    public function test_can_transition_new_to_confirmed_with_valid_data()
    {
        $order = $this->createValidOrder('new');

        $result = $this->stateMachine->transition($order, 'confirmed');

        $this->assertTrue($result);
        $this->assertEquals('confirmed', $order->fresh()->status);
    }

    public function test_cannot_transition_new_to_confirmed_without_items()
    {
        $order = $this->createOrder('new');
        // Order has no items

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Order must have at least one item');

        $this->stateMachine->transition($order, 'confirmed');
    }

    public function test_cannot_transition_new_to_confirmed_without_address()
    {
        $order = $this->createOrder('new');
        $order->items()->create([
            'sku' => 'TEST-001',
            'name' => 'Test Product',
            'qty' => 1,
            'price' => 100.00,
        ]);
        // Order has no addresses

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Order must have at least one address');

        $this->stateMachine->transition($order, 'confirmed');
    }

    public function test_can_transition_confirmed_to_paid_with_payment_id()
    {
        $order = $this->createValidOrder('confirmed');

        $result = $this->stateMachine->transition($order, 'paid', [
            'pmi_id' => $order->pmi_id
        ]);

        $this->assertTrue($result);
        $this->assertEquals('paid', $order->fresh()->status);
    }

    public function test_cannot_transition_confirmed_to_paid_without_payment_id()
    {
        $order = $this->createValidOrder('confirmed');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Payment ID (pmi_id) is required');

        $this->stateMachine->transition($order, 'paid', []);
    }

    public function test_get_available_transitions()
    {
        $order = $this->createOrder('new');
        $transitions = $this->stateMachine->getAvailableTransitions($order);

        $this->assertEquals(['confirmed', 'cancelled', 'on_hold'], $transitions);
    }

    public function test_invalid_state_transition_throws_exception()
    {
        $order = $this->createOrder('new');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid transition from 'new' to 'fulfilled'");

        $this->stateMachine->transition($order, 'fulfilled');
    }

    public function test_order_model_integration()
    {
        $order = $this->createValidOrder('new');

        $this->assertTrue($order->canTransitionTo('confirmed'));
        $this->assertFalse($order->canTransitionTo('paid'));
        $this->assertEquals(['confirmed', 'cancelled', 'on_hold'], $order->getAvailableTransitions());

        $order->transitionTo('confirmed');
        $this->assertEquals('confirmed', $order->fresh()->status);
    }

    protected function createOrder(string $status = 'new'): Order
    {
        $client = Client::create([
            'email' => 'test@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        return Order::create([
            'number' => Order::generateOrderNumber(),
            'pmi_id' => Order::generatePmiId(),
            'client_id' => $client->id,
            'status' => $status,
            'total_amount' => 100.00,
            'currency' => 'EUR',
        ]);
    }

    protected function createValidOrder(string $status = 'new'): Order
    {
        $client = Client::create([
            'email' => 'test@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $address = Address::create([
            'type' => 'shipping',
            'client_id' => $client->id,
            'name' => 'John Doe',
            'street1' => '123 Test St',
            'city' => 'Test City',
            'postal_code' => '12345',
            'country_code' => 'US',
        ]);

        $order = Order::create([
            'number' => Order::generateOrderNumber(),
            'pmi_id' => Order::generatePmiId(),
            'client_id' => $client->id,
            'status' => $status,
            'total_amount' => 100.00,
            'currency' => 'EUR',
            'shipping_address_id' => $address->id,
        ]);

        $order->items()->create([
            'sku' => 'TEST-001',
            'name' => 'Test Product',
            'qty' => 1,
            'price' => 100.00,
        ]);

        return $order;
    }
}
