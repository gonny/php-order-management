<?php

namespace Tests\Feature;

use App\Jobs\GenerateOrderPdfJob;
use App\Models\ApiClient;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OrderPdfGenerationTest extends TestCase
{
    use RefreshDatabase;

    private ApiClient $apiClient;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test API client using factory
        $this->apiClient = ApiClient::factory()->create();
        
        // Fake storage and queue
        Storage::fake('pdfs');
        Queue::fake();
    }

    public function test_api_endpoint_validates_request_data(): void
    {
        $order = Order::factory()->create();

        // Test missing required fields
        $response = $this->withHmacAuth('POST', "/api/v1/orders/{$order->id}/pdf", [])
            ->postJson("/api/v1/orders/{$order->id}/pdf", []);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['images', 'cell_size', 'overlay_url']);
    }

    public function test_api_endpoint_validates_images_array(): void
    {
        $order = Order::factory()->create();

        // Test empty images array
        $response = $this->withHmacAuth('POST', "/api/v1/orders/{$order->id}/pdf", [
            'images' => [],
            'cell_size' => 200,
            'overlay_url' => 'https://cdn.domain.com/overlay.svg',
        ])->postJson("/api/v1/orders/{$order->id}/pdf", [
            'images' => [],
            'cell_size' => 200,
            'overlay_url' => 'https://cdn.domain.com/overlay.svg',
        ]);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['images']);

        // Test too many images (>9)
        $payload = [
            'images' => array_fill(0, 10, 'https://cf2.r2.link/test.png'),
            'cell_size' => 200,
            'overlay_url' => 'https://cdn.domain.com/overlay.svg',
        ];
        $response = $this->withHmacAuth('POST', "/api/v1/orders/{$order->id}/pdf", $payload)
            ->postJson("/api/v1/orders/{$order->id}/pdf", $payload);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['images']);
    }

    public function test_api_endpoint_validates_cell_size_range(): void
    {
        $order = Order::factory()->create();

        // Test cell size too small
        $payload = [
            'images' => ['https://cf2.r2.link/test.png'],
            'cell_size' => 50, // below minimum
            'overlay_url' => 'https://cdn.domain.com/overlay.svg',
        ];
        $response = $this->withHmacAuth('POST', "/api/v1/orders/{$order->id}/pdf", $payload)
            ->postJson("/api/v1/orders/{$order->id}/pdf", $payload);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cell_size']);

        // Test cell size too large
        $payload = [
            'images' => ['https://cf2.r2.link/test.png'],
            'cell_size' => 700, // above maximum
            'overlay_url' => 'https://cdn.domain.com/overlay.svg',
        ];
        $response = $this->withHmacAuth('POST', "/api/v1/orders/{$order->id}/pdf", $payload)
            ->postJson("/api/v1/orders/{$order->id}/pdf", $payload);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cell_size']);
    }

    public function test_api_endpoint_validates_url_format(): void
    {
        $order = Order::factory()->create();

        // Test invalid image URL
        $payload = [
            'images' => ['not-a-valid-url'],
            'cell_size' => 200,
            'overlay_url' => 'https://cdn.domain.com/overlay.svg',
        ];
        $response = $this->withHmacAuth('POST', "/api/v1/orders/{$order->id}/pdf", $payload)
            ->postJson("/api/v1/orders/{$order->id}/pdf", $payload);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['images.0']);

        // Test invalid overlay URL
        $payload = [
            'images' => ['https://cf2.r2.link/test.png'],
            'cell_size' => 200,
            'overlay_url' => 'not-a-valid-url',
        ];
        $response = $this->withHmacAuth('POST', "/api/v1/orders/{$order->id}/pdf", $payload)
            ->postJson("/api/v1/orders/{$order->id}/pdf", $payload);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['overlay_url']);
    }

    public function test_api_endpoint_requires_authentication(): void
    {
        $order = Order::factory()->create();

        // Test without authentication headers
        $response = $this->postJson("/api/v1/orders/{$order->id}/pdf", [
            'images' => ['https://cf2.r2.link/test.png'],
            'cell_size' => 200,
            'overlay_url' => 'https://cdn.domain.com/overlay.svg',
        ]);

        $response->assertStatus(401);
    }

    public function test_pdf_download_requires_authentication(): void
    {
        $order = Order::factory()->create(['pdf_path' => 'test.pdf']);
        Storage::disk('pdfs')->put('test.pdf', 'fake-pdf-content');

        // Test without authentication
        $response = $this->get("/orders/{$order->id}/pdf");
        $response->assertStatus(302); // Redirect to login
    }

    public function test_pdf_download_returns_404_when_no_pdf(): void
    {
        $order = Order::factory()->create(); // No pdf_path set
        $user = \App\Models\User::factory()->create();

        $response = $this->actingAs($user)->get("/orders/{$order->id}/pdf");
        $response->assertStatus(404);
    }

    protected function withHmacAuth(string $method, string $path, array $body = []): self
    {
        $timestamp = time();
        $bodyJson = json_encode($body);
        
        // Get the plain text secret from meta (stored by factory for testing)
        $secret = $this->apiClient->meta['secret'] ?? 'fallback-secret';
        
        // Create HMAC signature
        $digest = 'SHA-256=' . base64_encode(hash('sha256', $bodyJson, true));
        $stringToSign = implode("\n", [
            $method,
            $path,
            $timestamp,
            $digest,
        ]);
        $signature = base64_encode(hash_hmac('sha256', $stringToSign, $secret, true));

        return $this->withHeaders([
            'X-Key-Id' => $this->apiClient->key_id,
            'X-Signature' => $signature,
            'X-Timestamp' => $timestamp,
            'Digest' => $digest,
            'Content-Type' => 'application/json',
        ]);
    }
}
