<?php

namespace App\Services\OrderManagement;

use App\Models\Order;
use App\Services\OrderManagement\Exceptions\InvalidOrderTransitionException;
use Illuminate\Support\Facades\DB;

class OrderStateMachine
{
    /**
     * Valid state transitions map
     */
    private const TRANSITIONS = [
        Order::STATUS_NEW => [
            Order::STATUS_CONFIRMED,
            Order::STATUS_CANCELLED,
            Order::STATUS_ON_HOLD,
            Order::STATUS_FAILED,
        ],
        Order::STATUS_CONFIRMED => [
            Order::STATUS_PAID,
            Order::STATUS_CANCELLED,
            Order::STATUS_ON_HOLD,
            Order::STATUS_FAILED,
        ],
        Order::STATUS_PAID => [
            Order::STATUS_FULFILLED,
            Order::STATUS_CANCELLED,
            Order::STATUS_ON_HOLD,
            Order::STATUS_FAILED,
        ],
        Order::STATUS_FULFILLED => [
            Order::STATUS_COMPLETED,
            Order::STATUS_CANCELLED,
            Order::STATUS_FAILED,
        ],
        Order::STATUS_ON_HOLD => [
            Order::STATUS_NEW,
            Order::STATUS_CONFIRMED,
            Order::STATUS_PAID,
            Order::STATUS_CANCELLED,
            Order::STATUS_FAILED,
        ],
        Order::STATUS_COMPLETED => [],
        Order::STATUS_CANCELLED => [],
        Order::STATUS_FAILED => [
            Order::STATUS_NEW,
            Order::STATUS_ON_HOLD,
        ],
    ];

    /**
     * Validation rules for each transition
     */
    private const VALIDATION_RULES = [
        Order::STATUS_NEW => [
            Order::STATUS_CONFIRMED => 'validateOrderConfirmation',
        ],
        Order::STATUS_CONFIRMED => [
            Order::STATUS_PAID => 'validatePaymentReceived',
        ],
        Order::STATUS_PAID => [
            Order::STATUS_FULFILLED => 'validateLabelGenerated',
        ],
        Order::STATUS_FULFILLED => [
            Order::STATUS_COMPLETED => 'validateDeliveryConfirmed',
        ],
    ];

    public function __construct(
        private AuditLogger $auditLogger
    ) {}

    /**
     * Check if a transition is valid
     */
    public function canTransition(Order $order, string $toStatus): bool
    {
        $allowedTransitions = self::TRANSITIONS[$order->status] ?? [];
        return in_array($toStatus, $allowedTransitions);
    }

    /**
     * Execute a state transition with validation and audit logging
     */
    public function transition(
        Order $order,
        string $toStatus,
        ?string $reason = null,
        ?array $metadata = null,
        ?string $actorType = 'api',
        ?string $actorId = null
    ): Order {
        if (!$this->canTransition($order, $toStatus)) {
            throw new InvalidOrderTransitionException(
                "Cannot transition order {$order->id} from {$order->status} to {$toStatus}"
            );
        }

        // Run validation if required
        $this->validateTransition($order, $toStatus);

        return DB::transaction(function () use ($order, $toStatus, $reason, $metadata, $actorType, $actorId) {
            $oldStatus = $order->status;
            $oldOrder = $order->toArray();

            // Update the order status
            $order->update(['status' => $toStatus]);
            $order->refresh();

            // Log the transition
            $this->auditLogger->logStateTransition(
                $order,
                $oldStatus,
                $toStatus,
                $reason,
                $metadata,
                $actorType,
                $actorId
            );

            // Dispatch background job to handle post-transition actions
            \App\Jobs\ProcessOrderStateChange::dispatch(
                $order,
                $oldStatus,
                $toStatus,
                $reason ?? 'Status transition',
                $metadata ?? []
            );

            return $order;
        });
    }

    /**
     * Get all possible transitions for an order
     */
    public function getAvailableTransitions(Order $order): array
    {
        return self::TRANSITIONS[$order->status] ?? [];
    }

    /**
     * Validate a specific transition
     */
    private function validateTransition(Order $order, string $toStatus): void
    {
        $validationMethod = self::VALIDATION_RULES[$order->status][$toStatus] ?? null;

        if ($validationMethod && method_exists($this, $validationMethod)) {
            $this->$validationMethod($order);
        }
    }

    /**
     * Validate order confirmation requirements
     */
    private function validateOrderConfirmation(Order $order): void
    {
        if (!$order->client) {
            throw new InvalidOrderTransitionException('Order must have a valid client');
        }

        if ($order->items()->count() === 0) {
            throw new InvalidOrderTransitionException('Order must have at least one item');
        }

        if (!$order->shipping_address_id && !$order->billing_address_id) {
            throw new InvalidOrderTransitionException('Order must have shipping or billing address');
        }

        if ($order->total_amount <= 0) {
            throw new InvalidOrderTransitionException('Order total must be greater than zero');
        }
    }

    /**
     * Validate payment received requirements
     */
    private function validatePaymentReceived(Order $order): void
    {
        if (empty($order->pmi_id)) {
            throw new InvalidOrderTransitionException('Order must have a valid payment method identifier (pmi_id)');
        }
    }

    /**
     * Validate label generation requirements
     */
    private function validateLabelGenerated(Order $order): void
    {
        if (!$order->carrier) {
            throw new InvalidOrderTransitionException('Order must have a carrier assigned');
        }

        if (!$order->shipping_address_id) {
            throw new InvalidOrderTransitionException('Order must have a shipping address');
        }

        // Check if we have an active shipping label
        $activeLabel = $order->shippingLabels()
            ->where('status', 'generated')
            ->exists();

        if (!$activeLabel) {
            throw new InvalidOrderTransitionException('Order must have a generated shipping label');
        }
    }

    /**
     * Validate delivery confirmation requirements
     */
    private function validateDeliveryConfirmed(Order $order): void
    {
        // This would typically check for delivery confirmation via carrier webhook
        // For now, we'll allow manual confirmation
    }

    /**
     * Get status display name for UI
     */
    public function getStatusDisplayName(string $status): string
    {
        return match ($status) {
            Order::STATUS_NEW => 'New',
            Order::STATUS_CONFIRMED => 'Confirmed',
            Order::STATUS_PAID => 'Paid',
            Order::STATUS_FULFILLED => 'Fulfilled',
            Order::STATUS_COMPLETED => 'Completed',
            Order::STATUS_CANCELLED => 'Cancelled',
            Order::STATUS_ON_HOLD => 'On Hold',
            Order::STATUS_FAILED => 'Failed',
            default => ucfirst($status),
        };
    }

    /**
     * Get status color for UI
     */
    public function getStatusColor(string $status): string
    {
        return match ($status) {
            Order::STATUS_NEW => 'blue',
            Order::STATUS_CONFIRMED => 'yellow',
            Order::STATUS_PAID => 'orange',
            Order::STATUS_FULFILLED => 'purple',
            Order::STATUS_COMPLETED => 'green',
            Order::STATUS_CANCELLED => 'gray',
            Order::STATUS_ON_HOLD => 'amber',
            Order::STATUS_FAILED => 'red',
            default => 'gray',
        };
    }
}
