<?php

namespace App\Services\OrderManagement;

use App\Models\AuditLog;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    /**
     * Log a state transition for an order
     */
    public function logStateTransition(
        Order $order,
        string $oldStatus,
        string $newStatus,
        ?string $reason = null,
        ?array $metadata = null,
        ?string $actorType = 'api',
        ?string $actorId = null
    ): AuditLog {
        $before = $order->getOriginal();
        $after = $order->getAttributes();

        // Detect actor if not provided
        if (!$actorId) {
            if ($actorType === 'user' && Auth::check()) {
                $actorId = Auth::id();
            } else {
                $actorId = 'system';
            }
        }

        return AuditLog::create([
            'actor_type' => $actorType,
            'actor_id' => $actorId,
            'action' => 'status_change',
            'entity_type' => 'order',
            'entity_id' => $order->id,
            'before' => [
                'status' => $oldStatus,
                'reason' => $reason,
                'metadata' => $metadata,
            ],
            'after' => [
                'status' => $newStatus,
                'reason' => $reason,
                'metadata' => $metadata,
            ],
        ]);
    }

    /**
     * Log any model change
     */
    public function logModelChange(
        string $entityType,
        string $entityId,
        string $action,
        array $before = [],
        array $after = [],
        ?string $actorType = 'api',
        ?string $actorId = null
    ): AuditLog {
        // Detect actor if not provided
        if (!$actorId) {
            if ($actorType === 'user' && Auth::check()) {
                $actorId = Auth::id();
            } else {
                $actorId = 'system';
            }
        }

        return AuditLog::create([
            'actor_type' => $actorType,
            'actor_id' => $actorId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'before' => $before,
            'after' => $after,
        ]);
    }

    /**
     * Log order creation
     */
    public function logOrderCreation(Order $order, ?string $actorType = 'api', ?string $actorId = null): AuditLog
    {
        return $this->logModelChange(
            'order',
            $order->id,
            'created',
            [],
            $order->toArray(),
            $actorType,
            $actorId
        );
    }

    /**
     * Log order update
     */
    public function logOrderUpdate(Order $order, array $originalData, ?string $actorType = 'api', ?string $actorId = null): AuditLog
    {
        return $this->logModelChange(
            'order',
            $order->id,
            'updated',
            $originalData,
            $order->toArray(),
            $actorType,
            $actorId
        );
    }

    /**
     * Log order deletion
     */
    public function logOrderDeletion(Order $order, ?string $actorType = 'api', ?string $actorId = null): AuditLog
    {
        return $this->logModelChange(
            'order',
            $order->id,
            'deleted',
            $order->toArray(),
            [],
            $actorType,
            $actorId
        );
    }
}
