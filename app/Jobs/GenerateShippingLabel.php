<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\ShippingLabel;
use App\Services\OrderManagement\OrderStateMachine;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GenerateShippingLabel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Order $order,
        public array $options = []
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Generating shipping label', [
            'order_id' => $this->order->id,
            'order_number' => $this->order->number,
            'carrier' => $this->order->carrier,
        ]);

        try {
            $label = match ($this->order->carrier) {
                'balikovna' => $this->generateBalikovnaLabel(),
                'dpd' => $this->generateDpdLabel(),
                default => throw new \InvalidArgumentException("Unsupported carrier: {$this->order->carrier}")
            };

            // Update order if we successfully generated the label
            if ($label && $this->order->status === Order::STATUS_PAID) {
                $stateMachine = app(OrderStateMachine::class);
                $stateMachine->transition(
                    $this->order,
                    Order::STATUS_FULFILLED,
                    'Shipping label generated',
                    ['label_id' => $label->id]
                );
            }

            Log::info('Shipping label generated successfully', [
                'order_id' => $this->order->id,
                'label_id' => $label?->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to generate shipping label', [
                'order_id' => $this->order->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function generateBalikovnaLabel(): ShippingLabel
    {
        $shippingAddress = $this->order->shippingAddress;

        if (!$shippingAddress) {
            throw new \InvalidArgumentException('Order missing shipping address');
        }

        // Simulate Balíkovna API call
        $payload = [
            'order_id' => $this->order->number,
            'recipient' => [
                'name' => $shippingAddress->name,
                'phone' => $shippingAddress->phone,
                'email' => $this->order->client->email,
            ],
            'address' => [
                'street1' => $shippingAddress->street1,
                'street2' => $shippingAddress->street2,
                'city' => $shippingAddress->city,
                'postal_code' => $shippingAddress->postal_code,
                'country_code' => $shippingAddress->country_code,
            ],
            'packages' => [[
                'weight' => $this->options['weight'] ?? 1000, // grams
                'length' => $this->options['length'] ?? 20,   // cm
                'width' => $this->options['width'] ?? 15,     // cm
                'height' => $this->options['height'] ?? 10,   // cm
            ]],
            'services' => $this->options['services'] ?? [],
        ];

        // In a real implementation, this would be an actual API call:
        // $response = Http::withToken(config('services.balikovna.api_key'))
        //     ->post(config('services.balikovna.api_url') . '/labels', $payload);

        // Simulate successful response
        $response = [
            'tracking_number' => 'BAL' . str_pad(random_int(1, 999999999), 9, '0', STR_PAD_LEFT),
            'label_url' => 'https://balikovna.cz/labels/example.pdf',
            'pickup_point_id' => $this->options['pickup_point_id'] ?? 'CZ001',
            'estimated_delivery' => now()->addDays(3)->toDateString(),
        ];

        return $this->order->shippingLabels()->create([
            'carrier' => 'balikovna',
            'tracking_number' => $response['tracking_number'],
            'label_url' => $response['label_url'],
            'status' => 'created',
            'meta' => array_merge($payload, $response),
        ]);
    }

    private function generateDpdLabel(): ShippingLabel
    {
        $shippingAddress = $this->order->shippingAddress;

        if (!$shippingAddress) {
            throw new \InvalidArgumentException('Order missing shipping address');
        }

        // Simulate DPD API call
        $payload = [
            'order_id' => $this->order->number,
            'recipient' => [
                'name' => $shippingAddress->name,
                'phone' => $shippingAddress->phone,
                'email' => $this->order->client->email,
            ],
            'address' => [
                'street1' => $shippingAddress->street1,
                'street2' => $shippingAddress->street2,
                'city' => $shippingAddress->city,
                'postal_code' => $shippingAddress->postal_code,
                'country_code' => $shippingAddress->country_code,
            ],
            'packages' => [[
                'weight' => $this->options['weight'] ?? 1000, // grams
                'length' => $this->options['length'] ?? 20,   // cm
                'width' => $this->options['width'] ?? 15,     // cm
                'height' => $this->options['height'] ?? 10,   // cm
            ]],
            'services' => $this->options['services'] ?? [],
        ];

        // In a real implementation, this would be an actual API call:
        // $response = Http::withToken(config('services.dpd.api_key'))
        //     ->post(config('services.dpd.api_url') . '/shipments', $payload);

        // Simulate successful response
        $response = [
            'tracking_number' => 'DPD' . str_pad(random_int(1, 999999999), 9, '0', STR_PAD_LEFT),
            'label_url' => 'https://dpd.com/labels/example.pdf',
            'service_type' => $this->options['service_type'] ?? 'standard',
            'estimated_delivery' => now()->addDays(2)->toDateString(),
        ];

        return $this->order->shippingLabels()->create([
            'carrier' => 'dpd',
            'tracking_number' => $response['tracking_number'],
            'label_url' => $response['label_url'],
            'status' => 'created',
            'meta' => array_merge($payload, $response),
        ]);
    }
}
