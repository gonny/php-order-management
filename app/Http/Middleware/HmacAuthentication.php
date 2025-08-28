<?php

namespace App\Http\Middleware;

use App\Models\ApiClient;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HmacAuthentication
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only apply to API routes, but exclude health endpoint
        if (!$request->is('api/*') || $request->is('api/v1/health')) {
            return $next($request);
        }

        try {
            $this->validateRequest($request);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Authentication failed',
                'message' => $e->getMessage()
            ], 401);
        }

        return $next($request);
    }

    protected function validateRequest(Request $request): void
    {
        // Extract required headers
        $keyId = $request->header('X-Key-Id');
        $signature = $request->header('X-Signature');
        $timestamp = $request->header('X-Timestamp');
        $digest = $request->header('Digest');

        if (!$keyId || !$signature || !$timestamp || !$digest) {
            throw new \Exception('Missing required headers: X-Key-Id, X-Signature, X-Timestamp, Digest');
        }

        // Find API client
        $apiClient = ApiClient::where('key_id', $keyId)
            ->where('active', true)
            ->first();

        if (!$apiClient) {
            throw new \Exception('Invalid API key');
        }

        // Check IP allowlist
        $clientIp = $request->ip();
        if (!$apiClient->isIpAllowed($clientIp)) {
            throw new \Exception('IP address not allowed');
        }

        // Validate timestamp (prevent replay attacks)
        $currentTime = time();
        $requestTime = (int) $timestamp;
        
        if (abs($currentTime - $requestTime) > 300) { // 5 minutes tolerance
            throw new \Exception('Request timestamp too old or too far in future');
        }

        // Validate body digest
        $body = $request->getContent();
        $expectedDigest = 'SHA-256=' . base64_encode(hash('sha256', $body, true));
        
        if (!hash_equals($expectedDigest, $digest)) {
            throw new \Exception('Invalid body digest');
        }

        // Validate HMAC signature
        $method = $request->method();
        $path = $request->getPathInfo();
        $stringToSign = $method . $path . $timestamp . $digest;
        
        // Use the stored secret hash directly for signature verification
        $expectedSignature = base64_encode(hash_hmac('sha256', $stringToSign, $apiClient->secret_hash, true));
        
        if (!hash_equals($expectedSignature, $signature)) {
            throw new \Exception('Invalid HMAC signature');
        }

        // Store API client in request for later use
        $request->attributes->set('api_client', $apiClient);
    }
}
