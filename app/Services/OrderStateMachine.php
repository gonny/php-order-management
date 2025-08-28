<?php

namespace App\Services;

use App\Models\Order;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class OrderStateMachine
{
    /**
     * Valid order states
     */
    public const STATES = [
        'new',
        'confirmed',
        'paid',
        'fulfilled',
        'completed',
        'cancelled',
        'on_hold',
        'failed'
    ];

    /**
     * Valid state transitions
     */
    public const TRANSITIONS = [
        'new' => ['confirmed', 'cancelled', 'on_hold'],
        'confirmed' => ['paid', 'cancelled', 'on_hold'],
        'paid' => ['fulfilled', 'cancelled', 'on_hold'],
        'fulfilled' => ['completed', 'cancelled'],
        'completed' => [],
        'cancelled' => [],
        'on_hold' => ['confirmed', 'cancelled'],
        'failed' => ['new', 'cancelled']
    ];

    /**
     * Transition validation rules
     */
    public const TRANSITION_RULES = [
        'new' => [
            'confirmed' => ['validate_order_data', 'reserve_inventory'],
        ],
        'confirmed' => [
            'paid' => ['validate_payment'],
        ],
        'paid' => [
            'fulfilled' => ['validate_shipping_label'],
        ],
        'fulfilled' => [
            'completed' => ['validate_delivery'],
        ],
    ];

    /**
     * Check if a state transition is valid
     */
    public function canTransition(Order $order, string $toState): bool
    {
        if (!in_array($toState, self::STATES)) {
            return false;
        }

        $currentState = $order->status;
        $allowedTransitions = self::TRANSITIONS[$currentState] ?? [];

        return in_array($toState, $allowedTransitions);
    }

    /**
     * Transition an order to a new state
     */
    public function transition(Order $order, string $toState, array $context = []): bool
    {
        if (!$this->canTransition($order, $toState)) {
            throw new \InvalidArgumentException(
                "Invalid transition from '{$order->status}' to '{$toState}'"
            );
        }

        // Run validation rules for this transition
        $this->validateTransition($order, $toState, $context);

        $previousState = $order->status;
        $order->status = $toState;
        $order->save();

        // Log the state change
        $this->logStateChange($order, $previousState, $toState, $context);

        return true;
    }

    /**
     * Get all possible transitions from current state
     */
    public function getAvailableTransitions(Order $order): array
    {
        return self::TRANSITIONS[$order->status] ?? [];
    }

    /**
     * Validate transition rules
     */
    protected function validateTransition(Order $order, string $toState, array $context): void
    {
        $currentState = $order->status;
        $rules = self::TRANSITION_RULES[$currentState][$toState] ?? [];

        foreach ($rules as $rule) {
            $this->runValidationRule($order, $rule, $context);
        }
    }

    /**
     * Run individual validation rules
     */
    protected function runValidationRule(Order $order, string $rule, array $context): void
    {
        switch ($rule) {
            case 'validate_order_data':
                $this->validateOrderData($order);
                break;
            case 'reserve_inventory':
                $this->reserveInventory($order);
                break;
            case 'validate_payment':
                $this->validatePayment($order, $context);
                break;
            case 'validate_shipping_label':
                $this->validateShippingLabel($order);
                break;
            case 'validate_delivery':
                $this->validateDelivery($order, $context);
                break;
            default:
                throw new \InvalidArgumentException("Unknown validation rule: {$rule}");
        }
    }

    /**
     * Validate order has required data
     */
    protected function validateOrderData(Order $order): void
    {
        if (!$order->client_id) {
            throw new \Exception('Order must have a client');
        }

        if ($order->items->isEmpty()) {
            throw new \Exception('Order must have at least one item');
        }

        if (!$order->shipping_address_id && !$order->billing_address_id) {
            throw new \Exception('Order must have at least one address');
        }

        if ($order->total_amount <= 0) {
            throw new \Exception('Order total must be greater than zero');
        }
    }

    /**
     * Reserve inventory for order items
     */
    protected function reserveInventory(Order $order): void
    {
        // Hook for inventory management system
        // This would integrate with your inventory service
        foreach ($order->items as $item) {
            // Check stock availability
            // Reserve inventory
            // Update product quantities
        }
    }

    /**
     * Validate payment information
     */
    protected function validatePayment(Order $order, array $context): void
    {
        if (empty($context['pmi_id'])) {
            throw new \Exception('Payment ID (pmi_id) is required');
        }

        if ($order->pmi_id !== $context['pmi_id']) {
            throw new \Exception('Payment ID mismatch');
        }

        // Additional payment validation would go here
        // - Verify payment amount matches order total
        // - Check payment status with payment provider
        // - Validate payment method
    }

    /**
     * Validate shipping label exists
     */
    protected function validateShippingLabel(Order $order): void
    {
        if (!$order->shippingLabel || !$order->shippingLabel->isGenerated()) {
            throw new \Exception('Valid shipping label is required');
        }

        if (!$order->carrier) {
            throw new \Exception('Carrier must be specified');
        }
    }

    /**
     * Validate delivery confirmation
     */
    protected function validateDelivery(Order $order, array $context): void
    {
        // This would typically be called from carrier webhooks
        // or manual delivery confirmation
        if (empty($context['delivery_confirmed'])) {
            throw new \Exception('Delivery confirmation required');
        }
    }

    /**
     * Log state change to audit trail
     */
    protected function logStateChange(Order $order, string $from, string $to, array $context): void
    {
        $actorType = Auth::check() ? 'user' : 'api';
        $actorId = Auth::id() ?? ($context['api_client_id'] ?? 0);

        AuditLog::logAction(
            $actorType,
            $actorId,
            'status_change',
            'order',
            $order->id,
            ['status' => $from],
            ['status' => $to, 'context' => $context]
        );
    }
}