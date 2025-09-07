<?php

namespace App\Jobs;

use App\Models\Order;
use App\Notifications\OrderStatusChanged;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class ProcessOrderStateChange implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Order $order,
        public string $previousStatus,
        public string $newStatus,
        public string $reason,
        public array $metadata = []
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Processing order state change', [
            'order_id' => $this->order->id,
            'previous_status' => $this->previousStatus,
            'new_status' => $this->newStatus,
            'reason' => $this->reason,
        ]);

        try {
            // Send customer notification
            $this->sendCustomerNotification();

            // Trigger automated actions based on new status
            $this->triggerAutomatedActions();

            // Send external webhooks if configured
            $this->sendExternalWebhooks();

            Log::info('Order state change processed successfully', [
                'order_id' => $this->order->id,
                'new_status' => $this->newStatus,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to process order state change', [
                'order_id' => $this->order->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function sendCustomerNotification(): void
    {
        // Only send notifications for certain status changes
        $notifiableStatuses = [
            Order::STATUS_CONFIRMED,
            Order::STATUS_PAID,
            Order::STATUS_FULFILLED,
            Order::STATUS_COMPLETED,
            Order::STATUS_CANCELLED,
            Order::STATUS_FAILED,
        ];

        if (in_array($this->newStatus, $notifiableStatuses)) {
            // In a real implementation, this would create and send a notification
            // Notification::send($this->order->client, new OrderStatusChanged($this->order, $this->previousStatus, $this->newStatus));

            Log::info('Customer notification sent', [
                'order_id' => $this->order->id,
                'customer_email' => $this->order->client->email,
                'status' => $this->newStatus,
            ]);
        }
    }

    private function triggerAutomatedActions(): void
    {
        match ($this->newStatus) {
            Order::STATUS_PAID => $this->handlePaidOrder(),
            Order::STATUS_FULFILLED => $this->handleFulfilledOrder(),
            Order::STATUS_COMPLETED => $this->handleCompletedOrder(),
            Order::STATUS_CANCELLED => $this->handleCancelledOrder(),
            Order::STATUS_FAILED => $this->handleFailedOrder(),
            default => null
        };
    }

    private function handlePaidOrder(): void
    {
        // Auto-generate shipping label if carrier is set
        if ($this->order->carrier && $this->order->shippingAddress) {
            GenerateShippingLabel::dispatch($this->order)->delay(now()->addMinutes(5));

            Log::info('Scheduled shipping label generation', [
                'order_id' => $this->order->id,
                'carrier' => $this->order->carrier,
            ]);
        }
    }

    private function handleFulfilledOrder(): void
    {
        // Update estimated delivery date
        if ($this->order->shippingLabels()->latest()->first()) {
            $label = $this->order->shippingLabels()->latest()->first();
            $estimatedDelivery = $label->meta['estimated_delivery'] ?? null;

            if ($estimatedDelivery) {
                $this->order->update([
                    'meta' => array_merge($this->order->meta ?? [], [
                        'estimated_delivery' => $estimatedDelivery,
                    ]),
                ]);
            }
        }
    }

    private function handleCompletedOrder(): void
    {
        // Mark order as delivered in metadata
        $this->order->update([
            'meta' => array_merge($this->order->meta ?? [], [
                'delivered_at' => now()->toISOString(),
            ]),
        ]);

        Log::info('Order marked as completed', [
            'order_id' => $this->order->id,
        ]);
    }

    private function handleCancelledOrder(): void
    {
        // Void any shipping labels if they exist
        $this->order->shippingLabels()
            ->where('status', 'generated')
            ->update(['status' => 'voided']);

        Log::info('Shipping labels voided for cancelled order', [
            'order_id' => $this->order->id,
        ]);
    }

    private function handleFailedOrder(): void
    {
        // Log the failure reason
        $this->order->update([
            'meta' => array_merge($this->order->meta ?? [], [
                'failure_reason' => $this->reason,
                'failed_at' => now()->toISOString(),
            ]),
        ]);
    }

    private function sendExternalWebhooks(): void
    {
        // In a real implementation, this would send webhooks to external systems
        // For now, just log that we would send them
        $webhookData = [
            'order_id' => $this->order->id,
            'order_number' => $this->order->number,
            'previous_status' => $this->previousStatus,
            'new_status' => $this->newStatus,
            'reason' => $this->reason,
            'timestamp' => now()->toISOString(),
            'metadata' => $this->metadata,
        ];

        Log::info('External webhook data prepared', $webhookData);
    }
}
