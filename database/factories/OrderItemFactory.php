<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
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
            'sku' => $this->faker->unique()->regexify('SKU-[A-Z0-9]{8}'),
            'name' => $this->faker->words(3, true),
            'qty' => $this->faker->numberBetween(1, 10),
            'price' => $this->faker->randomFloat(2, 5, 500),
            'tax_rate' => $this->faker->randomFloat(4, 0, 0.25),
            'meta' => null,
        ];
    }
}
