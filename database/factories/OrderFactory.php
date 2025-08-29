<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\Client;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'number' => 'ORD-' . $this->faker->unique()->numerify('######'),
            'pmi_id' => 'pmi_' . $this->faker->unique()->uuid(),
            'client_id' => Client::factory(),
            'status' => Order::STATUS_NEW,
            'total_amount' => $this->faker->randomFloat(2, 10, 1000),
            'currency' => 'USD',
            'shipping_address_id' => Address::factory()->shipping(),
            'billing_address_id' => Address::factory()->billing(),
            'carrier' => $this->faker->randomElement([Order::CARRIER_BALIKOVNA, Order::CARRIER_DPD]),
            'meta' => null,
        ];
    }

    public function statusNew()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => Order::STATUS_NEW,
            ];
        });
    }

    public function confirmed()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => Order::STATUS_CONFIRMED,
            ];
        });
    }

    public function paid()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => Order::STATUS_PAID,
            ];
        });
    }

    public function fulfilled()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => Order::STATUS_FULFILLED,
            ];
        });
    }

    public function completed()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => Order::STATUS_COMPLETED,
            ];
        });
    }

    public function cancelled()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => Order::STATUS_CANCELLED,
            ];
        });
    }
}
