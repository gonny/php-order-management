<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
 */
class AddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => $this->faker->randomElement(['shipping', 'billing']),
            'client_id' => Client::factory(),
            'name' => $this->faker->name(),
            'street1' => $this->faker->streetAddress(),
            'street2' => $this->faker->optional()->secondaryAddress(),
            'city' => $this->faker->city(),
            'postal_code' => $this->faker->postcode(),
            'country_code' => $this->faker->countryCode(),
            'state' => $this->faker->optional()->state(),
            'company' => $this->faker->optional()->company(),
            'phone' => $this->faker->optional()->phoneNumber(),
            'email' => $this->faker->optional()->email(),
        ];
    }

    public function shipping()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'shipping',
            ];
        });
    }

    public function billing()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'billing',
            ];
        });
    }
}
