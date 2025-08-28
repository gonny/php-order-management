<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class ApiRateLimit
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only apply to API routes
        if (!$request->is('api/*')) {
            return $next($request);
        }

        $apiClient = $request->attributes->get('api_client');
        
        if (!$apiClient) {
            // If no API client (shouldn't happen after HMAC auth), use IP
            $identifier = $request->ip();
        } else {
            $identifier = 'api_client_' . $apiClient->id;
        }

        $key = 'rate_limit:' . $identifier;
        $maxRequests = 60; // 60 requests per minute
        $window = 60; // 1 minute window

        $attempts = Cache::get($key, 0);

        if ($attempts >= $maxRequests) {
            return response()->json([
                'error' => 'Rate limit exceeded',
                'message' => "Maximum {$maxRequests} requests per minute allowed"
            ], 429);
        }

        // Increment counter
        Cache::put($key, $attempts + 1, $window);

        $response = $next($request);

        // Add rate limit headers
        $response->headers->set('X-RateLimit-Limit', $maxRequests);
        $response->headers->set('X-RateLimit-Remaining', max(0, $maxRequests - $attempts - 1));
        $response->headers->set('X-RateLimit-Reset', time() + $window);

        return $response;
    }
}
