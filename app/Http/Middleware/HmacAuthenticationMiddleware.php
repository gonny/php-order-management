<?php

namespace App\Http\Middleware;

use App\Models\ApiClient;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class HmacAuthenticationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip authentication for health check
        if ($request->is('api/v1/health')) {
            return $next($request);
        }

        try {
            $this->validateHmacSignature($request);
        } catch (\Exception $e) {
            Log::warning('HMAC authentication failed', [
                'ip' => $request->ip(),
                'path' => $request->path(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Authentication failed',
                'message' => 'Invalid signature or authentication headers',
            ], 401);
        }

        return $next($request);
    }

    /**
     * Validate HMAC signature
     */
    private function validateHmacSignature(Request $request): void
    {
        // Required headers
        $keyId = $request->header('X-Key-Id');
        $signature = $request->header('X-Signature');
        $timestamp = $request->header('X-Timestamp');
        $digest = $request->header('Digest');

        if (!$keyId || !$signature || !$timestamp || !$digest) {
            throw new \Exception('Missing required authentication headers');
        }

        // Find API client
        $apiClient = ApiClient::active()
            ->where('key_id', $keyId)
            ->first();

        if (!$apiClient) {
            throw new \Exception('Invalid API key');
        }

        // Check IP allowlist if configured
        if (!$apiClient->isIpAllowed($request->ip())) {
            throw new \Exception('IP address not allowed');
        }

        // Validate timestamp (prevent replay attacks)
        $requestTime = Carbon::createFromTimestamp($timestamp);
        $now = Carbon::now();

        if ($requestTime->diffInSeconds($now) > 300) { // 5 minute tolerance
            throw new \Exception('Request timestamp is too old or too far in the future');
        }

        // Validate digest
        $body = $request->getContent();
        $expectedDigest = 'SHA-256=' . base64_encode(hash('sha256', $body, true));

        if (!hash_equals($expectedDigest, $digest)) {
            throw new \Exception('Invalid digest');
        }

        // Validate HMAC signature
        $stringToSign = implode("\n", [
            $request->method(),
            $request->getRequestUri(),
            $timestamp,
            $digest,
        ]);

        $expectedSignature = base64_encode(hash_hmac('sha256', $stringToSign, $apiClient->secret_hash, true));

        if (!hash_equals($expectedSignature, $signature)) {
            throw new \Exception('Invalid HMAC signature');
        }

        // Store authenticated API client in request
        $request->attributes->set('api_client', $apiClient);
    }
}
