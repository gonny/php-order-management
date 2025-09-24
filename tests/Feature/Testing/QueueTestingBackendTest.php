<?php

namespace Tests\Feature\Testing;

use App\Models\ApiClient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class QueueTestingBackendTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private ApiClient $apiClient;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user
        $this->user = User::factory()->create();
        
        // Create test API client
        $this->apiClient = ApiClient::create([
            'name' => 'Test API Client',
            'key_id' => 'test_key_123',
            'secret_hash' => hash('sha256', 'test_secret'),
            'active' => true,
            'ip_allowlist' => ['127.0.0.1'],
        ]);
    }

    public function test_execute_api_test_validates_required_fields()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/testing/api-test/execute', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['method', 'endpoint', 'api_client_id']);
    }

    public function test_execute_api_test_with_valid_data()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/testing/api-test/execute', [
                'method' => 'GET',
                'endpoint' => '/api/v1/health',
                'api_client_id' => $this->apiClient->id,
                'payload' => '{}',
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'request' => [
                'method',
                'endpoint',
                'payload',
                'headers',
                'timestamp',
            ],
            'response' => [
                'status_code',
                'headers',
                'body',
                'execution_time',
                'timestamp',
            ],
        ]);
    }

    public function test_execute_api_test_generates_correct_hmac_headers()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/testing/api-test/execute', [
                'method' => 'POST',
                'endpoint' => '/api/v1/orders',
                'api_client_id' => $this->apiClient->id,
                'payload' => '{"test": "data"}',
            ]);

        $response->assertStatus(200);
        
        $responseData = $response->json();

        // Verify required headers exist
        $this->assertArrayHasKey('request', $responseData);
        $this->assertArrayHasKey('headers', $responseData['request']);
        
        $headers = $responseData['request']['headers'];
        
        $this->assertArrayHasKey('X-Key-Id', $headers);
        $this->assertArrayHasKey('X-Signature', $headers);
        $this->assertArrayHasKey('X-Timestamp', $headers);
        $this->assertArrayHasKey('Digest', $headers);
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertArrayHasKey('Accept', $headers);

        // Verify the headers contain expected values
        $this->assertEquals('test_key_123', $headers['X-Key-Id']);
        $this->assertEquals('application/json', $headers['Content-Type']);
    }

    public function test_clear_failed_jobs_functionality()
    {
        // Add a failed job
        DB::table('failed_jobs')->insert([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'connection' => 'database',
            'queue' => 'default',
            'payload' => json_encode(['displayName' => 'App\\Jobs\\TestJob']),
            'exception' => 'Test exception',
            'failed_at' => now(),
        ]);

        $this->assertEquals(1, DB::table('failed_jobs')->count());

        $response = $this->actingAs($this->user)
            ->deleteJson('/testing/queue/failed/clear');

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'All failed jobs cleared successfully',
        ]);

        $this->assertEquals(0, DB::table('failed_jobs')->count());
    }

    public function test_retry_all_failed_jobs_functionality()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/testing/queue/failed/retry-all');

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'All failed jobs queued for retry',
        ]);
    }

    public function test_unauthenticated_users_cannot_access_testing_routes()
    {
        $response = $this->postJson('/testing/api-test/execute', []);
        $response->assertStatus(401);

        $response = $this->deleteJson('/testing/queue/failed/clear');
        $response->assertStatus(401);

        $response = $this->postJson('/testing/queue/failed/retry-all');
        $response->assertStatus(401);
    }

    public function test_api_client_validation_in_test_execution()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/testing/api-test/execute', [
                'method' => 'GET',
                'endpoint' => '/api/v1/health',
                'api_client_id' => 999999, // Non-existent API client
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['api_client_id']);
    }

    public function test_payload_json_validation()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/testing/api-test/execute', [
                'method' => 'POST',
                'endpoint' => '/api/v1/orders',
                'api_client_id' => $this->apiClient->id,
                'payload' => 'invalid json string',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['payload']);
    }

    public function test_hmac_signature_generation_consistency()
    {
        $method = 'POST';
        $endpoint = '/api/v1/test';
        $body = '{"test":"data"}';

        // Make two identical requests with a small delay to ensure different timestamps
        $response1 = $this->actingAs($this->user)
            ->postJson('/testing/api-test/execute', [
                'method' => $method,
                'endpoint' => $endpoint,
                'api_client_id' => $this->apiClient->id,
                'payload' => $body,
            ]);

        // Add a delay to ensure different timestamps
        usleep(1000000); // 1 second

        $response2 = $this->actingAs($this->user)
            ->postJson('/testing/api-test/execute', [
                'method' => $method,
                'endpoint' => $endpoint,
                'api_client_id' => $this->apiClient->id,
                'payload' => $body,
            ]);

        $response1->assertStatus(200);
        $response2->assertStatus(200);

        $data1 = $response1->json();
        $data2 = $response2->json();

        // Headers should be different due to different timestamps
        $this->assertNotEquals(
            $data1['request']['headers']['X-Timestamp'],
            $data2['request']['headers']['X-Timestamp']
        );

        // But the structure should be the same
        $this->assertEquals(
            array_keys($data1['request']['headers']),
            array_keys($data2['request']['headers'])
        );
    }

    public function test_hmac_signature_format_validation()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/testing/api-test/execute', [
                'method' => 'POST',
                'endpoint' => '/api/v1/test',
                'api_client_id' => $this->apiClient->id,
                'payload' => '{"key": "value"}',
            ]);

        $response->assertStatus(200);
        $data = $response->json();

        // Verify HMAC signature format
        $this->assertIsString($data['request']['headers']['X-Signature']);
        $this->assertIsNumeric($data['request']['headers']['X-Timestamp']);
        $this->assertStringStartsWith('SHA-256=', $data['request']['headers']['Digest']);
        
        // Verify signature is base64 encoded
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9+\/]*={0,2}$/', $data['request']['headers']['X-Signature']);
    }

    public function test_queue_testing_controller_dependency_injection()
    {
        // Test that controller can be instantiated (dependency injection works)
        $controller = app(\App\Http\Controllers\Testing\QueueTestingController::class);
        $this->assertInstanceOf(\App\Http\Controllers\Testing\QueueTestingController::class, $controller);
    }
}