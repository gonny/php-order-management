<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\Webhook;
use App\Services\OrderManagement\OrderStateMachine;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Webhook $webhook
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Processing webhook', [
            'webhook_id' => $this->webhook->id,
            'source' => $this->webhook->source,
            'event' => $this->webhook->event,
        ]);

        try {
            match ($this->webhook->source) {
                'balikovna' => $this->handleBalikovnaWebhook(),
                'dpd' => $this->handleDpdWebhook(),
                'payment' => $this->handlePaymentWebhook(),
                default => throw new \InvalidArgumentException("Unknown webhook source: {$this->webhook->source}")
            };

            $this->webhook->update([
                'status' => 'processed',
                'processed_at' => now(),
            ]);

            Log::info('Webhook processed successfully', [
                'webhook_id' => $this->webhook->id,
            ]);

        } catch (\Exception $e) {
            $this->webhook->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            Log::error('Webhook processing failed', [
                'webhook_id' => $this->webhook->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function handleBalikovnaWebhook(): void
    {
        $payload = (array) $this->webhook->payload;
        $orderId = $payload['order_id'] ?? null;

        if (!$orderId) {
            throw new \InvalidArgumentException('Missing order_id in Balíkovna webhook');
        }

        $order = Order::where('id', $orderId)
            ->orWhere('number', $orderId)
            ->orWhere('pmi_id', $orderId)
            ->firstOrFail();

        match ($this->webhook->event) {
            'label_created' => $this->handleLabelCreated($order, $payload),
            'package_delivered' => $this->handlePackageDelivered($order, $payload),
            'package_returned' => $this->handlePackageReturned($order, $payload),
            default => Log::warning("Unknown Balíkovna event: {$this->webhook->event}")
        };
    }

    private function handleDpdWebhook(): void
    {
        $payload = $this->webhook->payload;
        $orderId = $payload['order_id'] ?? null;

        if (!$orderId) {
            throw new \InvalidArgumentException('Missing order_id in DPD webhook');
        }

        $order = Order::where('id', $orderId)
            ->orWhere('number', $orderId)
            ->orWhere('pmi_id', $orderId)
            ->firstOrFail();

        match ($this->webhook->event) {
            'label_created' => $this->handleLabelCreated($order, $payload),
            'package_delivered' => $this->handlePackageDelivered($order, $payload),
            'package_returned' => $this->handlePackageReturned($order, $payload),
            default => Log::warning("Unknown DPD event: {$this->webhook->event}")
        };
    }

    private function handlePaymentWebhook(): void
    {
        $payload = $this->webhook->payload;
        $pmiId = $payload['pmi_id'] ?? null;

        if (!$pmiId) {
            throw new \InvalidArgumentException('Missing pmi_id in payment webhook');
        }

        $order = Order::where('pmi_id', $pmiId)->firstOrFail();

        match ($this->webhook->event) {
            'payment_confirmed' => $this->handlePaymentConfirmed($order, $payload),
            'payment_failed' => $this->handlePaymentFailed($order, $payload),
            default => Log::warning("Unknown payment event: {$this->webhook->event}")
        };
    }

    private function handleLabelCreated(Order $order, array $payload): void
    {
        $order->shippingLabels()->create([
            'carrier' => $payload['carrier'] ?? $order->carrier,
            'tracking_number' => $payload['tracking_number'],
            'label_url' => $payload['label_url'] ?? null,
            'status' => 'created',
            'meta' => $payload,
        ]);

        if ($order->status === Order::STATUS_PAID) {
            $stateMachine = app(OrderStateMachine::class);
            $stateMachine->transition(
                $order,
                Order::STATUS_FULFILLED,
                'Shipping label created via webhook',
                $payload
            );
        }
    }

    private function handlePackageDelivered(Order $order, array $payload): void
    {
        if ($order->status === Order::STATUS_FULFILLED) {
            $stateMachine = app(OrderStateMachine::class);
            $stateMachine->transition(
                $order,
                Order::STATUS_COMPLETED,
                'Package delivered via webhook',
                $payload
            );
        }
    }

    private function handlePackageReturned(Order $order, array $payload): void
    {
        $stateMachine = app(OrderStateMachine::class);
        $stateMachine->transition(
            $order,
            Order::STATUS_FAILED,
            'Package returned via webhook',
            $payload
        );
    }

    private function handlePaymentConfirmed(Order $order, array $payload): void
    {
        if ($order->status === Order::STATUS_CONFIRMED) {
            $stateMachine = app(OrderStateMachine::class);
            $stateMachine->transition(
                $order,
                Order::STATUS_PAID,
                'Payment confirmed via webhook',
                $payload
            );
        }
    }

    private function handlePaymentFailed(Order $order, array $payload): void
    {
        $stateMachine = app(OrderStateMachine::class);
        $stateMachine->transition(
            $order,
            Order::STATUS_FAILED,
            'Payment failed via webhook',
            $payload
        );
    }
}
