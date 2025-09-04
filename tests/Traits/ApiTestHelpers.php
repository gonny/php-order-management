<?php

namespace Tests\Traits;

use App\Models\ApiClient;
use Illuminate\Testing\TestResponse;

trait ApiTestHelpers
{
    protected ApiClient $apiClient;
    protected string $secret;

    /**
     * Set up API client for testing
     */
    protected function setUpApiClient(): void
    {
        $this->secret = 'test-secret-key-for-hmac-authentication';
        
        $this->apiClient = ApiClient::factory()->create([
            'key_id' => 'test-client-key',
            'secret_hash' => $this->secret, // Store raw secret
            'name' => 'Test API Client',
            'active' => true,
            'ip_allowlist' => null, // Allow all IPs for testing
        ]);
    }

    /**
     * Make authenticated API request with HMAC signature
     */
    protected function authenticatedJson(string $method, string $uri, array $data = [], array $headers = []): TestResponse
    {
        if (!isset($this->apiClient)) {
            $this->setUpApiClient();
        }

        $timestamp = time();
        $body = json_encode($data);
        $digest = 'SHA-256=' . base64_encode(hash('sha256', $body, true));

        // Create string to sign
        $stringToSign = implode("\n", [
            strtoupper($method),
            $uri,
            $timestamp,
            $digest,
        ]);

        // Generate HMAC signature
        $signature = base64_encode(hash_hmac('sha256', $stringToSign, $this->secret, true));

        $authHeaders = [
            'X-Key-Id' => $this->apiClient->key_id,
            'X-Signature' => $signature,
            'X-Timestamp' => $timestamp,
            'Digest' => $digest,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        return $this->json($method, $uri, $data, array_merge($authHeaders, $headers));
    }

    /**
     * Make authenticated GET request
     */
    protected function authenticatedGet(string $uri, array $headers = []): TestResponse
    {
        return $this->authenticatedJson('GET', $uri, [], $headers);
    }

    /**
     * Make authenticated POST request
     */
    protected function authenticatedPost(string $uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->authenticatedJson('POST', $uri, $data, $headers);
    }

    /**
     * Make authenticated PUT request
     */
    protected function authenticatedPut(string $uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->authenticatedJson('PUT', $uri, $data, $headers);
    }

    /**
     * Make authenticated PATCH request
     */
    protected function authenticatedPatch(string $uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->authenticatedJson('PATCH', $uri, $data, $headers);
    }

    /**
     * Make authenticated DELETE request
     */
    protected function authenticatedDelete(string $uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->authenticatedJson('DELETE', $uri, $data, $headers);
    }
}