<?php

namespace Database\Factories;

use App\Models\ApiClient;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ApiClientFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = ApiClient::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $secret = Str::random(64);
        
        return [
            'key_id' => 'client_' . Str::random(10),
            'secret_hash' => $secret, // Store raw secret for HMAC verification
            'name' => $this->faker->company(),
            'ip_allowlist' => ['127.0.0.1', '::1'], // Store as array, will be cast to JSON
            'active' => true,
            'last_used_at' => null,
            'meta' => [
                'created_by' => 'factory',
                'environment' => 'testing',
            ],
        ];
    }

    /**
     * Indicate that the API client is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the API client has been used recently.
     */
    public function recentlyUsed(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_used_at' => now()->subMinutes(rand(1, 60)),
        ]);
    }

    /**
     * Set a specific key ID for the API client.
     */
    public function withKeyId(string $keyId): static
    {
        return $this->state(fn (array $attributes) => [
            'key_id' => $keyId,
        ]);
    }

    /**
     * Set a specific secret for the API client.
     */
    public function withSecret(string $secret): static
    {
        return $this->state(fn (array $attributes) => [
            'secret' => $secret,
        ]);
    }
}