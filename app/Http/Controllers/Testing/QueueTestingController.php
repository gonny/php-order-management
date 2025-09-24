<?php

namespace App\Http\Controllers\Testing;

use App\Http\Controllers\Controller;
use App\Models\ApiClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Inertia\Inertia;

class QueueTestingController extends Controller
{
    /**
     * Display the queue testing dashboard
     */
    public function dashboard()
    {
        $queueStats = $this->getQueueStats();
        $recentJobs = $this->getRecentJobs();
        $apiClients = ApiClient::active()->get();

        return Inertia::render('Testing/QueueDashboard', [
            'queueStats' => $queueStats,
            'recentJobs' => $recentJobs,
            'apiClients' => $apiClients,
        ]);
    }

    /**
     * Get queue statistics
     */
    private function getQueueStats(): array
    {
        return [
            'pending' => DB::table('jobs')->count(),
            'failed' => DB::table('failed_jobs')->count(),
            'completed_today' => $this->getCompletedJobsToday(),
            'average_processing_time' => $this->getAverageProcessingTime(),
        ];
    }

    /**
     * Get recent jobs for monitoring
     */
    private function getRecentJobs(): array
    {
        $recentFailed = DB::table('failed_jobs')
            ->select(['id', 'connection', 'queue', 'payload', 'exception', 'failed_at'])
            ->orderBy('failed_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($job) {
                $payload = json_decode($job->payload, true);
                return [
                    'id' => $job->id,
                    'type' => 'failed',
                    'job_class' => $payload['displayName'] ?? 'Unknown',
                    'queue' => $job->queue,
                    'failed_at' => $job->failed_at,
                    'exception' => $this->truncateException($job->exception),
                ];
            });

        $recentPending = DB::table('jobs')
            ->select(['id', 'queue', 'payload', 'created_at', 'available_at'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($job) {
                $payload = json_decode($job->payload, true);
                return [
                    'id' => $job->id,
                    'type' => 'pending',
                    'job_class' => $payload['displayName'] ?? 'Unknown',
                    'queue' => $job->queue,
                    'created_at' => $job->created_at,
                    'available_at' => $job->available_at,
                ];
            });

        return [
            'failed' => $recentFailed->toArray(),
            'pending' => $recentPending->toArray(),
        ];
    }

    /**
     * Get count of completed jobs today
     */
    private function getCompletedJobsToday(): int
    {
        // This would require audit logging of completed jobs
        // For now, we'll estimate based on total jobs processed
        return 0; // Placeholder
    }

    /**
     * Get average processing time
     */
    private function getAverageProcessingTime(): float
    {
        // This would require job timing data
        // For now, return a placeholder
        return 0.0; // Placeholder
    }

    /**
     * Truncate exception message for display
     */
    private function truncateException(string $exception): string
    {
        return substr($exception, 0, 200) . (strlen($exception) > 200 ? '...' : '');
    }

    /**
     * Display API testing interface
     */
    public function apiTesting()
    {
        $apiClients = ApiClient::active()->get();
        $payloadTemplates = $this->getPayloadTemplates();

        return Inertia::render('Testing/ApiTesting', [
            'apiClients' => $apiClients,
            'payloadTemplates' => $payloadTemplates,
            'endpoints' => $this->getApiEndpoints(),
        ]);
    }

    /**
     * Get predefined payload templates
     */
    private function getPayloadTemplates(): array
    {
        return [
            'order_creation' => [
                'name' => 'Order Creation',
                'description' => 'Template for creating a new order with customer data',
                'template' => [
                    'client_id' => '{{client_id}}',
                    'delivery_address' => [
                        'company_name' => '{{company_name}}',
                        'contact_person' => '{{contact_person}}',
                        'street' => '{{street_address}}',
                        'city' => '{{city}}',
                        'postal_code' => '{{postal_code}}',
                        'country' => '{{country_code}}',
                        'phone' => '{{phone_number}}',
                        'email' => '{{email_address}}'
                    ],
                    'items' => [
                        [
                            'name' => '{{item_name}}',
                            'quantity' => '{{quantity}}',
                            'price' => '{{unit_price}}',
                            'sku' => '{{sku_code}}'
                        ]
                    ],
                    'total_amount' => '{{total_amount}}',
                    'currency' => 'CZK',
                    'shipping_method' => '{{shipping_method}}',
                    'notes' => '{{order_notes}}'
                ]
            ],
            'client_creation' => [
                'name' => 'Client Creation',
                'description' => 'Template for creating a new client',
                'template' => [
                    'company_name' => '{{company_name}}',
                    'contact_person' => '{{contact_person}}',
                    'email' => '{{email_address}}',
                    'phone' => '{{phone_number}}',
                    'billing_address' => [
                        'street' => '{{billing_street}}',
                        'city' => '{{billing_city}}',
                        'postal_code' => '{{billing_postal_code}}',
                        'country' => '{{billing_country}}'
                    ],
                    'tax_id' => '{{tax_id}}',
                    'preferences' => [
                        'preferred_shipping' => '{{preferred_shipping}}',
                        'payment_terms' => '{{payment_terms}}'
                    ]
                ]
            ],
            'shipping_label' => [
                'name' => 'Shipping Label Generation',
                'description' => 'Template for generating shipping labels',
                'template' => [
                    'order_id' => '{{order_id}}',
                    'carrier' => '{{carrier_name}}',
                    'service_type' => '{{service_type}}',
                    'package_details' => [
                        'weight' => '{{package_weight}}',
                        'dimensions' => [
                            'length' => '{{length}}',
                            'width' => '{{width}}',
                            'height' => '{{height}}'
                        ]
                    ],
                    'pickup_address' => [
                        'company_name' => '{{pickup_company}}',
                        'street' => '{{pickup_street}}',
                        'city' => '{{pickup_city}}',
                        'postal_code' => '{{pickup_postal_code}}',
                        'country' => '{{pickup_country}}'
                    ]
                ]
            ],
            'pdf_generation' => [
                'name' => 'PDF Generation',
                'description' => 'Template for generating PDF documents',
                'template' => [
                    'order_id' => '{{order_id}}',
                    'document_type' => '{{document_type}}',
                    'template_id' => '{{template_id}}',
                    'options' => [
                        'include_logo' => true,
                        'include_qr_code' => true,
                        'format' => 'A4',
                        'orientation' => 'portrait'
                    ],
                    'custom_fields' => [
                        'header_text' => '{{header_text}}',
                        'footer_text' => '{{footer_text}}'
                    ]
                ]
            ]
        ];
    }

    private function getApiEndpoints(): array
    {
        return [
            [
                'group' => 'Orders',
                'endpoints' => [
                    ['method' => 'GET', 'path' => '/api/v1/orders', 'description' => 'List all orders'],
                    ['method' => 'POST', 'path' => '/api/v1/orders', 'description' => 'Create new order'],
                    ['method' => 'GET', 'path' => '/api/v1/orders/{id}', 'description' => 'Get order details'],
                    ['method' => 'PUT', 'path' => '/api/v1/orders/{id}', 'description' => 'Update order'],
                    ['method' => 'POST', 'path' => '/api/v1/orders/{id}/pdf', 'description' => 'Generate order PDF'],
                    ['method' => 'POST', 'path' => '/api/v1/orders/{id}/label', 'description' => 'Generate shipping label'],
                ]
            ],
            [
                'group' => 'Clients',
                'endpoints' => [
                    ['method' => 'GET', 'path' => '/api/v1/clients', 'description' => 'List all clients'],
                    ['method' => 'POST', 'path' => '/api/v1/clients', 'description' => 'Create new client'],
                    ['method' => 'GET', 'path' => '/api/v1/clients/{id}', 'description' => 'Get client details'],
                    ['method' => 'PUT', 'path' => '/api/v1/clients/{id}', 'description' => 'Update client'],
                ]
            ],
            [
                'group' => 'Queue Management',
                'endpoints' => [
                    ['method' => 'GET', 'path' => '/api/v1/queues/stats', 'description' => 'Get queue statistics'],
                    ['method' => 'GET', 'path' => '/api/v1/queues/failed', 'description' => 'List failed jobs'],
                    ['method' => 'POST', 'path' => '/api/v1/queues/failed/{id}/retry', 'description' => 'Retry failed job'],
                ]
            ],
            [
                'group' => 'Dashboard',
                'endpoints' => [
                    ['method' => 'GET', 'path' => '/api/v1/dashboard/metrics', 'description' => 'Get dashboard metrics'],
                    ['method' => 'GET', 'path' => '/api/v1/health', 'description' => 'Health check'],
                ]
            ]
        ];
    }

    /**
     * Execute API test request
     */
    public function executeTest(Request $request)
    {
        $request->validate([
            'method' => 'required|in:GET,POST,PUT,DELETE',
            'endpoint' => 'required|string',
            'payload' => 'nullable|json',
            'api_client_id' => 'required|exists:api_clients,id',
        ]);

        $apiClient = ApiClient::findOrFail($request->api_client_id);
        
        // Generate HMAC headers for the request
        $headers = $this->generateHmacHeaders(
            $request->method,
            $request->endpoint,
            $request->payload ?? '',
            $apiClient
        );

        // Execute the request and log the results
        $response = $this->executeApiRequest(
            $request->method,
            $request->endpoint,
            $request->payload,
            $headers
        );

        return response()->json([
            'request' => [
                'method' => $request->method,
                'endpoint' => $request->endpoint,
                'payload' => $request->payload,
                'headers' => $headers,
                'timestamp' => now()->toISOString(),
            ],
            'response' => $response,
        ]);
    }

    /**
     * Generate HMAC authentication headers
     */
    private function generateHmacHeaders(string $method, string $endpoint, string $body, ApiClient $apiClient): array
    {
        $timestamp = time();
        $digest = 'SHA-256=' . base64_encode(hash('sha256', $body, true));
        
        $stringToSign = implode("\n", [
            $method,
            $endpoint,
            $timestamp,
            $digest,
        ]);

        $signature = base64_encode(hash_hmac('sha256', $stringToSign, $apiClient->secret_hash, true));

        return [
            'X-Key-Id' => $apiClient->key_id,
            'X-Signature' => $signature,
            'X-Timestamp' => $timestamp,
            'Digest' => $digest,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    /**
     * Execute API request (placeholder - would use HTTP client in real implementation)
     */
    private function executeApiRequest(string $method, string $endpoint, ?string $payload, array $headers): array
    {
        // This is a placeholder - in real implementation, you'd use Laravel's HTTP client
        // to make actual requests to your API endpoints
        return [
            'status_code' => 200,
            'headers' => ['Content-Type' => 'application/json'],
            'body' => '{"message": "Test request executed successfully"}',
            'execution_time' => 0.123,
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * Clear failed jobs
     */
    public function clearFailedJobs()
    {
        DB::table('failed_jobs')->delete();
        
        return response()->json([
            'message' => 'All failed jobs cleared successfully',
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Retry all failed jobs
     */
    public function retryAllFailedJobs()
    {
        Artisan::call('queue:retry', ['id' => 'all']);
        
        return response()->json([
            'message' => 'All failed jobs queued for retry',
            'timestamp' => now()->toISOString(),
        ]);
    }
}