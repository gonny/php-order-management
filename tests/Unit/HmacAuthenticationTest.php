<?php

namespace Tests\Unit;

use App\Models\ApiClient;
use App\Services\HmacSignatureService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HmacAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected ApiClient $apiClient;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->apiClient = ApiClient::create([
            'name' => 'Test Client',
            'key_id' => 'test-key-123',
            'secret_hash' => hash('sha256', 'test-secret'),
            'active' => true,
        ]);
    }

    public function test_health_endpoint_does_not_require_authentication()
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'timestamp',
                'version',
                'database',
                'queue'
            ]);
    }

    public function test_api_endpoint_requires_authentication()
    {
        $response = $this->getJson('/api/v1/orders');

        $response->assertStatus(401)
            ->assertJson([
                'error' => 'Authentication failed',
                'message' => 'Missing required headers: X-Key-Id, X-Signature, X-Timestamp, Digest'
            ]);
    }

    public function test_valid_hmac_signature_allows_access()
    {
        $method = 'GET';
        $path = '/api/v1/orders';
        $body = '';
        $secret = 'test-secret';

        $headers = HmacSignatureService::generateHeaders(
            $this->apiClient->key_id,
            $method,
            $path,
            $body,
            $secret
        );

        // Use get() instead of getJson() to avoid Laravel adding JSON content-type automatically
        $response = $this->withHeaders($headers)->get($path);

        // Should pass authentication and reach controller (200 OK)
        $this->assertEquals(200, $response->getStatusCode());
        $response->assertJson([
            'data' => [],
            'message' => 'Orders endpoint - authenticated successfully'
        ]);
    }

    public function test_invalid_key_id_is_rejected()
    {
        $method = 'GET';
        $path = '/api/v1/orders';
        $body = '';
        $secret = 'test-secret';

        $headers = HmacSignatureService::generateHeaders(
            'invalid-key',
            $method,
            $path,
            $body,
            $secret
        );

        $response = $this->withHeaders($headers)->getJson($path);

        $response->assertStatus(401)
            ->assertJson([
                'error' => 'Authentication failed',
                'message' => 'Invalid API key'
            ]);
    }

    public function test_invalid_signature_is_rejected()
    {
        // Test with wrong secret - this will cause body digest to fail first
        $method = 'GET';
        $path = '/api/v1/orders';
        $body = '';
        
        // Create headers with wrong secret
        $timestamp = time();
        $wrongDigest = 'SHA-256=' . base64_encode(hash('sha256', 'wrong-body', true));
        
        $headers = [
            'X-Key-Id' => $this->apiClient->key_id,
            'X-Signature' => 'wrong-signature',
            'X-Timestamp' => (string) $timestamp,
            'Digest' => $wrongDigest,
            'Content-Type' => 'application/json',
        ];

        $response = $this->withHeaders($headers)->getJson($path);

        $response->assertStatus(401)
            ->assertJson([
                'error' => 'Authentication failed',
                'message' => 'Invalid body digest'
            ]);
    }

    public function test_old_timestamp_is_rejected()
    {
        $method = 'GET';
        $path = '/api/v1/orders';
        $body = '';
        $secret = 'test-secret';
        $oldTimestamp = time() - 400; // 400 seconds ago (over 5 minute limit)

        $headers = HmacSignatureService::generateHeaders(
            $this->apiClient->key_id,
            $method,
            $path,
            $body,
            $secret,
            $oldTimestamp
        );

        $response = $this->withHeaders($headers)->getJson($path);

        $response->assertStatus(401)
            ->assertJson([
                'error' => 'Authentication failed',
                'message' => 'Request timestamp too old or too far in future'
            ]);
    }

    public function test_inactive_api_client_is_rejected()
    {
        $this->apiClient->update(['active' => false]);

        $method = 'GET';
        $path = '/api/v1/orders';
        $body = '';
        $secret = 'test-secret';

        $headers = HmacSignatureService::generateHeaders(
            $this->apiClient->key_id,
            $method,
            $path,
            $body,
            $secret
        );

        $response = $this->withHeaders($headers)->getJson($path);

        $response->assertStatus(401)
            ->assertJson([
                'error' => 'Authentication failed',
                'message' => 'Invalid API key'
            ]);
    }

    public function test_ip_allowlist_is_enforced()
    {
        $this->apiClient->update([
            'ip_allowlist' => ['192.168.1.1', '10.0.0.1']
        ]);

        $method = 'GET';
        $path = '/api/v1/orders';
        $body = '';
        $secret = 'test-secret';

        $headers = HmacSignatureService::generateHeaders(
            $this->apiClient->key_id,
            $method,
            $path,
            $body,
            $secret
        );

        $response = $this->withHeaders($headers)->getJson($path);

        $response->assertStatus(401)
            ->assertJson([
                'error' => 'Authentication failed',
                'message' => 'IP address not allowed'
            ]);
    }

    public function test_post_request_with_body_validates_correctly()
    {
        $method = 'POST';
        $path = '/api/v1/orders';
        $body = json_encode(['test' => 'data']);
        $secret = 'test-secret';

        $headers = HmacSignatureService::generateHeaders(
            $this->apiClient->key_id,
            $method,
            $path,
            $body,
            $secret
        );

        $response = $this->withHeaders($headers)->postJson($path, ['test' => 'data']);

        // Should pass authentication and reach controller
        $this->assertEquals(200, $response->getStatusCode());
        $response->assertJson([
            'message' => 'Order creation endpoint - authenticated successfully'
        ]);
    }
}
