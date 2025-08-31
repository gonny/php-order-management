<?php

namespace App\Services\Shipping;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DpdApiService
{
    private string $apiUrl;
    private string $apiKey;
    private array $credentials;
    private bool $testMode;

    public function __construct()
    {
        $this->apiUrl = config('shipping.carriers.dpd.api_url', 'https://geoapi.dpd.cz/v1');
        $this->apiKey = config('shipping.carriers.dpd.api_key');
        $this->testMode = config('shipping.carriers.dpd.test_mode', true);
        
        // DPD uses username/password authentication
        $this->credentials = [
            'username' => config('shipping.carriers.dpd.username'),
            'password' => config('shipping.carriers.dpd.password')
        ];
    }

    /**
     * Create a DPD shipment and return shipment details
     */
    public function createShipment(array $shipmentData): array
    {
        $this->checkRateLimit();
        
        $payload = $this->prepareShipmentPayload($shipmentData);
        
        Log::info('DPD API: Creating shipment', [
            'payload' => $payload,
            'test_mode' => $this->testMode
        ]);

        try {
            $response = $this->makeApiRequest('POST', '/shipment', $payload);
            
            Log::info('DPD API: Shipment created successfully', [
                'shipment_id' => $response['shipment_id'] ?? 'unknown',
                'tracking_number' => $response['tracking_number'] ?? 'unknown'
            ]);
            
            return $response;
            
        } catch (\Exception $e) {
            Log::error('DPD API: Failed to create shipment', [
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);
            throw $e;
        }
    }

    /**
     * Download a shipping label PDF
     */
    public function downloadLabel(string $shipmentId): string
    {
        $this->checkRateLimit();
        
        Log::info('DPD API: Downloading label', ['shipment_id' => $shipmentId]);
        
        try {
            $response = Http::withBasicAuth($this->credentials['username'], $this->credentials['password'])
                ->timeout(30)
                ->get("{$this->apiUrl}/shipment/{$shipmentId}/label");
            
            if (!$response->successful()) {
                throw new \Exception("Failed to download label: " . $response->body());
            }
            
            return $response->body();
            
        } catch (\Exception $e) {
            Log::error('DPD API: Failed to download label', [
                'shipment_id' => $shipmentId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Delete/void a DPD shipment
     */
    public function deleteShipment(string $shipmentId): bool
    {
        $this->checkRateLimit();
        
        Log::info('DPD API: Deleting shipment', ['shipment_id' => $shipmentId]);
        
        try {
            $response = $this->makeApiRequest('DELETE', "/shipment/{$shipmentId}");
            
            Log::info('DPD API: Shipment deleted successfully', ['shipment_id' => $shipmentId]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('DPD API: Failed to delete shipment', [
                'shipment_id' => $shipmentId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get tracking information for a parcel
     */
    public function getTrackingInfo(string $parcelIdent): array
    {
        $this->checkRateLimit();
        
        Log::info('DPD API: Getting tracking info', ['parcel_ident' => $parcelIdent]);
        
        try {
            $response = $this->makeApiRequest('GET', "/parcels/{$parcelIdent}/tracking");
            
            Log::info('DPD API: Tracking info retrieved successfully', [
                'parcel_ident' => $parcelIdent,
                'status' => $response['status'] ?? 'unknown'
            ]);
            
            return $response;
            
        } catch (\Exception $e) {
            Log::error('DPD API: Failed to get tracking info', [
                'parcel_ident' => $parcelIdent,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get pickup points for a location
     */
    public function getPickupPoints(string $countryCode, string $postalCode): array
    {
        $this->checkRateLimit();
        
        Log::info('DPD API: Getting pickup points', [
            'country_code' => $countryCode,
            'postal_code' => $postalCode
        ]);
        
        try {
            $response = $this->makeApiRequest('GET', '/pickup-points', [
                'country_code' => $countryCode,
                'postal_code' => $postalCode,
                'limit' => 10
            ]);
            
            return $response['pickup_points'] ?? [];
            
        } catch (\Exception $e) {
            Log::error('DPD API: Failed to get pickup points', [
                'country_code' => $countryCode,
                'postal_code' => $postalCode,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Check rate limit before making API calls
     */
    private function checkRateLimit(): void
    {
        $rateLimitKey = 'dpd-api-rate-limit';
        
        if (!RateLimiter::attempt($rateLimitKey, 35, function() {
            return true;
        }, 60)) {
            $retryAfter = RateLimiter::availableIn($rateLimitKey);
            throw new \Exception("DPD API rate limit exceeded. Retry after {$retryAfter} seconds.");
        }
    }

    /**
     * Make authenticated API request with retry logic
     */
    private function makeApiRequest(string $method, string $endpoint, array $data = []): array
    {
        $attempt = 0;
        $maxAttempts = 3;
        
        while ($attempt < $maxAttempts) {
            try {
                $request = Http::withBasicAuth($this->credentials['username'], $this->credentials['password'])
                    ->timeout(30)
                    ->retry(3, 1000);
                
                $response = match (strtoupper($method)) {
                    'GET' => $request->get("{$this->apiUrl}{$endpoint}", $data),
                    'POST' => $request->post("{$this->apiUrl}{$endpoint}", $data),
                    'DELETE' => $request->delete("{$this->apiUrl}{$endpoint}"),
                    default => throw new \InvalidArgumentException("Unsupported HTTP method: {$method}")
                };
                
                if ($response->successful()) {
                    return $response->json();
                }
                
                // Handle specific HTTP error codes
                if ($response->status() === 429) {
                    $retryAfter = (int) $response->header('Retry-After', 60);
                    Log::warning('DPD API: Rate limited, waiting', ['retry_after' => $retryAfter]);
                    sleep($retryAfter);
                    $attempt++;
                    continue;
                }
                
                if ($response->status() >= 500) {
                    $attempt++;
                    if ($attempt < $maxAttempts) {
                        $delay = min(pow(2, $attempt), 30); // Exponential backoff, max 30 seconds
                        Log::warning('DPD API: Server error, retrying', [
                            'status' => $response->status(),
                            'attempt' => $attempt,
                            'delay' => $delay
                        ]);
                        sleep($delay);
                        continue;
                    }
                }
                
                // Client error - don't retry
                $errorData = $response->json();
                throw new \Exception("DPD API error: " . ($errorData['message'] ?? $response->body()));
                
            } catch (RequestException $e) {
                $attempt++;
                if ($attempt >= $maxAttempts) {
                    throw $e;
                }
                
                $delay = min(pow(2, $attempt), 30);
                Log::warning('DPD API: Request exception, retrying', [
                    'error' => $e->getMessage(),
                    'attempt' => $attempt,
                    'delay' => $delay
                ]);
                sleep($delay);
            }
        }
        
        throw new \Exception("DPD API: Maximum retry attempts exceeded");
    }

    /**
     * Prepare shipment payload for DPD API
     */
    private function prepareShipmentPayload(array $shipmentData): array
    {
        $serviceMap = [
            'DPD_Home' => '327',         // D-B2C: home delivery
            'DPD_PickupPoint' => '337'   // D-B2C-PSD: pickup point delivery
        ];
        
        $service = $serviceMap[$shipmentData['shipping_method']] ?? '327';
        
        $payload = [
            'service' => $service,
            'order_id' => $shipmentData['order_number'],
            'recipient' => [
                'name' => $shipmentData['recipient']['name'],
                'email' => $shipmentData['recipient']['email'],
                'phone' => $shipmentData['recipient']['phone']
            ],
            'address' => [
                'street' => $shipmentData['address']['street1'],
                'city' => $shipmentData['address']['city'],
                'postal_code' => $shipmentData['address']['postal_code'],
                'country_code' => strtoupper($shipmentData['address']['country_code'])
            ],
            'packages' => $shipmentData['packages']
        ];
        
        // Add pickup point for pickup point deliveries (both DPD and Balikovna pickup points)
        if (in_array($shipmentData['shipping_method'], ['DPD_PickupPoint', 'Balikovna_PickupPoint']) && !empty($shipmentData['pickup_point_id'])) {
            $payload['pickup_point_id'] = $shipmentData['pickup_point_id'];
        }
        
        // Add parcel group for consolidated shipments
        if (!empty($shipmentData['parcel_group_id'])) {
            $payload['parcel_group_id'] = $shipmentData['parcel_group_id'];
        }
        
        return $payload;
    }

    /**
     * Calculate package dimensions and weight based on order items
     */
    public static function calculatePackageDimensions(int $itemCount): array
    {
        $weightPerItem = 20; // grams
        $totalWeight = $itemCount * $weightPerItem;
        
        // 5x10x8 cm per 27 items
        $itemsPerPackage = 27;
        $packageCount = max(1, ceil($itemCount / $itemsPerPackage));
        
        $packages = [];
        for ($i = 0; $i < $packageCount; $i++) {
            $itemsInThisPackage = min($itemsPerPackage, $itemCount - ($i * $itemsPerPackage));
            $packageWeight = $itemsInThisPackage * $weightPerItem;
            
            $packages[] = [
                'weight' => $packageWeight,
                'length' => 10, // cm
                'width' => 5,   // cm
                'height' => 8,  // cm
            ];
        }
        
        return $packages;
    }
}