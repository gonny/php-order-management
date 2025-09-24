<?php

namespace Tests\Unit;

use App\Jobs\DownloadR2PhotosJob;
use App\Models\ApiClient;
use App\Models\Client;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class R2PdfGenerationApiTest extends TestCase
{
    use RefreshDatabase;

    private ApiClient $apiClient;

    protected function setUp(): void
    {
        parent::setUp();

        // Create API client for authentication
        $this->apiClient = ApiClient::factory()->create();
        Queue::fake();
    }

    protected function withHmacAuth(string $method = 'POST', string $path = '/api/v1/orders', array $body = []): self
    {
        $timestamp = time();
        $bodyJson = json_encode($body);
        $digest = 'SHA-256=' . base64_encode(hash('sha256', $bodyJson, true));

        // Create string to sign in correct format matching backend
        $uri = $path;
        $stringToSign = implode("\n", [
            $method,
            $uri,
            $timestamp,
            $digest,
        ]);

        $signature = base64_encode(hash_hmac('sha256', $stringToSign, $this->apiClient->secret_hash, true));

        return $this->withHeaders([
            'X-Key-Id' => $this->apiClient->key_id,
            'X-Signature' => $signature,
            'X-Timestamp' => $timestamp,
            'Digest' => $digest,
            'Content-Type' => 'application/json',
        ]);
    }

    protected function getValidOrderData(): array
    {
        return [
            'client' => [
                'external_id' => 'CLIENT_TEST_001',
                'email' => 'test@example.com',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'phone' => '+1234567890',
            ],
            'shipping_address' => [
                'name' => 'John Doe',
                'street1' => '123 Main St',
                'city' => 'New York',
                'postal_code' => '10001',
                'country_code' => 'US',
            ],
            'items' => [
                [
                    'sku' => 'TEST-001',
                    'name' => 'Test Product',
                    'qty' => 1,
                    'price' => 10.00,
                ],
            ],
        ];
    }

    public function test_can_create_order_with_r2_workflow(): void
    {
        $orderData = $this->getValidOrderData();
        $orderData['r2_photo_links'] = [
            'https://r2.example.com/photo1.jpg',
            'https://r2.example.com/photo2.jpg',
        ];
        $orderData['remote_session_id'] = 'test-session-123';

        $response = $this->withHmacAuth('POST', '/api/v1/orders', $orderData)
            ->postJson('/api/v1/orders', $orderData);

        if ($response->status() !== 201) {
            dump('Response status: ' . $response->status());
            dump('Response body: ' . $response->getContent());
        }

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'number',
                    'client',
                    'items',
                ],
                'message',
                'r2_workflow' => [
                    'status',
                    'remote_session_id',
                    'photos_count',
                ],
            ]);

        // Verify R2 workflow data
        $r2Workflow = $response->json('r2_workflow');
        $this->assertEquals('processing', $r2Workflow['status']);
        $this->assertEquals('test-session-123', $r2Workflow['remote_session_id']);
        $this->assertEquals(2, $r2Workflow['photos_count']);

        // Verify DownloadR2PhotosJob was dispatched
        Queue::assertPushed(DownloadR2PhotosJob::class, function ($job) {
            return $job->remoteSessionId === 'test-session-123' &&
                   count($job->r2PhotoLinks) === 2;
        });
    }

    public function test_can_create_order_without_r2_workflow(): void
    {
        $orderData = $this->getValidOrderData();

        $response = $this->withHmacAuth('POST', '/api/v1/orders', $orderData)
            ->postJson('/api/v1/orders', $orderData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'number',
                    'client',
                    'items',
                ],
                'message',
            ])
            ->assertJsonMissing(['r2_workflow']);

        // Verify no R2 job was dispatched
        Queue::assertNotPushed(DownloadR2PhotosJob::class);
    }

    public function test_validates_r2_photo_links_format(): void
    {
        $orderData = $this->getValidOrderData();
        $orderData['r2_photo_links'] = [
            'invalid-url',
            'https://r2.example.com/photo2.jpg',
        ];
        $orderData['remote_session_id'] = 'test-session-123';

        $response = $this->withHmacAuth('POST', '/api/v1/orders', $orderData)
            ->postJson('/api/v1/orders', $orderData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['r2_photo_links.0']);
    }

    public function test_can_trigger_r2_pdf_generation_for_existing_order(): void
    {
        $order = Order::factory()->create();

        $data = [
            'r2_photo_links' => [
                'https://r2.example.com/photo1.jpg',
                'https://r2.example.com/photo2.jpg',
                'https://r2.example.com/photo3.jpg',
            ],
            'remote_session_id' => 'test-session-456',
        ];

        $response = $this->withHmacAuth('POST', "/api/v1/orders/{$order->id}/pdf/r2", $data)
            ->postJson("/api/v1/orders/{$order->id}/pdf/r2", $data);

        $response->assertStatus(202)
            ->assertJsonStructure([
                'message',
                'order_id',
                'remote_session_id',
                'status',
            ]);

        $this->assertEquals('test-session-456', $response->json('remote_session_id'));
        $this->assertEquals('processing', $response->json('status'));

        // Verify DownloadR2PhotosJob was dispatched
        Queue::assertPushed(DownloadR2PhotosJob::class, function ($job) use ($order) {
            return $job->order->id === $order->id &&
                   $job->remoteSessionId === 'test-session-456' &&
                   count($job->r2PhotoLinks) === 3;
        });
    }
}
