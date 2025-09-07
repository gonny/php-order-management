<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class RateLimitApiMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, int $maxAttempts = 60): Response
    {
        $apiClient = $request->attributes->get('api_client');

        if (!$apiClient) {
            // If no API client is authenticated, use IP-based limiting
            $key = 'rate_limit:ip:' . $request->ip();
        } else {
            // Use API client key for rate limiting
            $key = 'rate_limit:client:' . $apiClient->key_id;
        }

        $attempts = Cache::get($key, 0);

        if ($attempts >= $maxAttempts) {
            return response()->json([
                'error' => 'Rate limit exceeded',
                'message' => "Too many requests. Maximum {$maxAttempts} requests per minute allowed.",
                'retry_after' => 60,
            ], 429)->header('Retry-After', 60);
        }

        // Increment the counter
        Cache::put($key, $attempts + 1, 60); // 1 minute expiry

        $response = $next($request);

        // Add rate limit headers
        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', max(0, $maxAttempts - $attempts - 1));
        $response->headers->set('X-RateLimit-Reset', time() + 60);

        return $response;
    }
}
