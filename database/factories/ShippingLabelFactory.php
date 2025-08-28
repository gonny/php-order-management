<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\ShippingLabel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ShippingLabel>
 */
class ShippingLabelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'carrier' => $this->faker->randomElement([Order::CARRIER_BALIKOVNA, Order::CARRIER_DPD]),
            'carrier_shipment_id' => $this->faker->unique()->regexify('[A-Z0-9]{12}'),
            'tracking_number' => $this->faker->unique()->regexify('[A-Z0-9]{15}'),
            'file_path' => 'labels/' . $this->faker->uuid() . '.pdf',
            'format' => ShippingLabel::FORMAT_PDF,
            'status' => ShippingLabel::STATUS_GENERATED,
            'raw_response' => [
                'carrier_response' => 'Mock response data',
                'timestamp' => now()->toISOString(),
            ],
            'meta' => null,
        ];
    }

    public function generated()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => ShippingLabel::STATUS_GENERATED,
            ];
        });
    }

    public function failed()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => ShippingLabel::STATUS_FAILED,
                'tracking_number' => null,
                'file_path' => null,
            ];
        });
    }

    public function voided()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => ShippingLabel::STATUS_VOIDED,
            ];
        });
    }
}
