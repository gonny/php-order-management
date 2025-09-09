<?php

namespace Tests\Feature\Testing;

use App\Models\ApiClient;
use App\Models\Client;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class QueueTestingSuiteTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private ApiClient $apiClient;
    private Client $testClient;
    private Order $testOrder;

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
            'is_active' => true,
            'allowed_ips' => json_encode(['127.0.0.1']),
            'rate_limit' => 100,
        ]);

        // Create test client and order
        $this->testClient = Client::factory()->create();
        $this->testOrder = Order::factory()->create([
            'client_id' => $this->testClient->id,
        ]);
    }

    public function test_queue_dashboard_loads_successfully()
    {
        $response = $this->actingAs($this->user)
            ->get('/testing/queue-dashboard');

        $response->assertStatus(200);
        $response->assertInertia(function ($page) {
            $page->component('Testing/QueueDashboard')
                ->has('queueStats')
                ->has('recentJobs')
                ->has('apiClients');
        });
    }

    public function test_queue_dashboard_shows_correct_statistics()
    {
        // Add some test jobs to database
        DB::table('jobs')->insert([
            'queue' => 'default',
            'payload' => json_encode(['job' => 'TestJob']),
            'attempts' => 0,
            'created_at' => now()->timestamp,
            'available_at' => now()->timestamp,
        ]);

        DB::table('failed_jobs')->insert([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'connection' => 'database',
            'queue' => 'default',
            'payload' => json_encode(['displayName' => 'App\\Jobs\\TestJob']),
            'exception' => 'Test exception',
            'failed_at' => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->get('/testing/queue-dashboard');

        $response->assertStatus(200);
        $response->assertInertia(function ($page) {
            $page->has('queueStats', function ($stats) {
                $stats->where('pending', 1)
                      ->where('failed', 1);
            });
        });
    }

    public function test_api_testing_interface_loads_with_templates()
    {
        $response = $this->actingAs($this->user)
            ->get('/testing/api-testing');

        $response->assertStatus(200);
        $response->assertInertia(function ($page) {
            $page->component('Testing/ApiTesting')
                ->has('apiClients')
                ->has('payloadTemplates')
                ->has('endpoints')
                ->has('payloadTemplates.order_creation')
                ->has('payloadTemplates.client_creation')
                ->has('payloadTemplates.shipping_label')
                ->has('payloadTemplates.pdf_generation');
        });
    }

    public function test_payload_templates_contain_required_fields()
    {
        $response = $this->actingAs($this->user)
            ->get('/testing/api-testing');

        $response->assertInertia(function ($page) {
            $page->has('payloadTemplates.order_creation.template', function ($template) {
                $template->has('client_id')
                        ->has('delivery_address')
                        ->has('items')
                        ->has('total_amount');
            });
        });
    }

    public function test_api_endpoints_are_properly_categorized()
    {
        $response = $this->actingAs($this->user)
            ->get('/testing/api-testing');

        $response->assertInertia(function ($page) {
            $page->has('endpoints.0', function ($group) {
                $group->where('group', 'Orders')
                      ->has('endpoints');
            });
        });
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
        $response->assertJson(function ($json) {
            $json->has('request.headers', function ($headers) {
                $headers->has('X-Key-Id')
                        ->has('X-Signature')
                        ->has('X-Timestamp')
                        ->has('Digest')
                        ->has('Content-Type')
                        ->has('Accept');
            });
        });

        // Verify the headers contain expected values
        $responseData = $response->json();
        $this->assertEquals('test_key_123', $responseData['request']['headers']['X-Key-Id']);
        $this->assertEquals('application/json', $responseData['request']['headers']['Content-Type']);
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
        $response = $this->get('/testing/queue-dashboard');
        $response->assertRedirect('/login');

        $response = $this->get('/testing/api-testing');
        $response->assertRedirect('/login');

        $response = $this->postJson('/testing/api-test/execute', []);
        $response->assertStatus(401);
    }

    public function test_queue_stats_calculation_accuracy()
    {
        // Clear any existing jobs
        DB::table('jobs')->delete();
        DB::table('failed_jobs')->delete();

        // Add specific number of jobs
        for ($i = 0; $i < 3; $i++) {
            DB::table('jobs')->insert([
                'queue' => 'default',
                'payload' => json_encode(['job' => "TestJob{$i}"]),
                'attempts' => 0,
                'created_at' => now()->timestamp,
                'available_at' => now()->timestamp,
            ]);
        }

        for ($i = 0; $i < 2; $i++) {
            DB::table('failed_jobs')->insert([
                'uuid' => \Illuminate\Support\Str::uuid(),
                'connection' => 'database',
                'queue' => 'default',
                'payload' => json_encode(['displayName' => "App\\Jobs\\FailedJob{$i}"]),
                'exception' => "Test exception {$i}",
                'failed_at' => now(),
            ]);
        }

        $response = $this->actingAs($this->user)
            ->get('/testing/queue-dashboard');

        $response->assertInertia(function ($page) {
            $page->has('queueStats', function ($stats) {
                $stats->where('pending', 3)
                      ->where('failed', 2);
            });
        });
    }

    public function test_recent_jobs_display_correct_information()
    {
        DB::table('failed_jobs')->insert([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'connection' => 'database',
            'queue' => 'test-queue',
            'payload' => json_encode(['displayName' => 'App\\Jobs\\TestFailedJob']),
            'exception' => 'Test exception message',
            'failed_at' => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->get('/testing/queue-dashboard');

        $response->assertInertia(function ($page) {
            $page->has('recentJobs.failed.0', function ($job) {
                $job->where('type', 'failed')
                    ->where('job_class', 'App\\Jobs\\TestFailedJob')
                    ->where('queue', 'test-queue')
                    ->has('exception');
            });
        });
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
}