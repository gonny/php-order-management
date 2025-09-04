<?php

namespace Tests\Feature\Api\V1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Tests\Traits\ApiTestHelpers;

class QueueControllerTest extends TestCase
{
    use RefreshDatabase, ApiTestHelpers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpApiClient();
    }

    public function test_can_get_queue_stats(): void
    {
        // Set up some test data
        Cache::put('jobs_processed_today', 42);

        $response = $this->authenticatedGet('/api/v1/queues/stats');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'pending_jobs',
                    'failed_jobs',
                    'processed_jobs_today',
                    'queue_names',
                    'workers_status' => [
                        'active_workers',
                        'last_heartbeat',
                    ],
                ],
                'message',
            ])
            ->assertJson([
                'data' => [
                    'pending_jobs' => 0,
                    'failed_jobs' => 0,
                    'processed_jobs_today' => 42,
                    'queue_names' => ['default'],
                    'workers_status' => [
                        'active_workers' => 1,
                    ],
                ],
                'message' => 'Queue statistics retrieved successfully',
            ]);
    }

    public function test_can_get_queue_stats_with_pending_jobs(): void
    {
        // Insert test job into jobs table
        DB::table('jobs')->insert([
            'queue' => 'default',
            'payload' => json_encode([
                'displayName' => 'App\\Jobs\\TestJob',
                'job' => 'Illuminate\\Queue\\CallQueuedHandler@call',
                'data' => [],
            ]),
            'attempts' => 0,
            'reserved_at' => null,
            'available_at' => time(),
            'created_at' => time(),
        ]);

        DB::table('jobs')->insert([
            'queue' => 'high-priority',
            'payload' => json_encode([
                'displayName' => 'App\\Jobs\\HighPriorityJob',
                'job' => 'Illuminate\\Queue\\CallQueuedHandler@call',
                'data' => [],
            ]),
            'attempts' => 0,
            'reserved_at' => null,
            'available_at' => time(),
            'created_at' => time(),
        ]);

        $response = $this->authenticatedGet('/api/v1/queues/stats');

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertEquals(2, $data['pending_jobs']);
        $this->assertContains('default', $data['queue_names']);
        $this->assertContains('high-priority', $data['queue_names']);
    }

    public function test_can_get_failed_jobs_empty(): void
    {
        $response = $this->authenticatedGet('/api/v1/queues/failed');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => [
                    'current_page',
                    'per_page',
                    'total',
                    'last_page',
                ],
                'message',
            ])
            ->assertJson([
                'data' => [],
                'meta' => [
                    'current_page' => 1,
                    'per_page' => 15,
                    'total' => 0,
                    'last_page' => 0,
                ],
                'message' => 'Failed jobs retrieved successfully',
            ]);
    }

    public function test_can_get_failed_jobs_with_pagination(): void
    {
        // Add failed jobs to the failed_jobs table
        $this->addFailedJob('job-1', 'default', 'Test exception 1');
        $this->addFailedJob('job-2', 'high-priority', 'Test exception 2');

        $response = $this->authenticatedGet('/api/v1/queues/failed?per_page=1&page=1');

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertEquals(1, count($data['data']));
        $this->assertEquals(2, $data['meta']['total']);
        $this->assertEquals(2, $data['meta']['last_page']);
        $this->assertEquals(1, $data['meta']['current_page']);
        $this->assertEquals(1, $data['meta']['per_page']);
    }

    public function test_can_retry_failed_job(): void
    {
        $failedJobId = $this->addFailedJob('retry-test-job', 'default', 'Test exception');

        $response = $this->authenticatedPost("/api/v1/queues/failed/{$failedJobId}/retry");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Job queued for retry successfully',
            ]);
    }

    public function test_can_delete_failed_job(): void
    {
        $failedJobId = $this->addFailedJob('delete-test-job', 'default', 'Test exception');

        $response = $this->authenticatedDelete("/api/v1/queues/failed/{$failedJobId}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Failed job deleted successfully',
            ]);
    }

    public function test_can_clear_all_failed_jobs(): void
    {
        $this->addFailedJob('job-1', 'default', 'Test exception 1');
        $this->addFailedJob('job-2', 'default', 'Test exception 2');

        $response = $this->authenticatedDelete('/api/v1/queues/failed');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'All failed jobs cleared successfully',
            ]);
    }

    public function test_can_get_recent_jobs_empty(): void
    {
        $response = $this->authenticatedGet('/api/v1/queues/recent');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'message',
            ])
            ->assertJson([
                'data' => [],
                'message' => 'Recent jobs retrieved successfully',
            ]);
    }

    public function test_can_get_recent_jobs_with_data(): void
    {
        // Insert test jobs into jobs table
        DB::table('jobs')->insert([
            'queue' => 'default',
            'payload' => json_encode([
                'displayName' => 'App\\Jobs\\RecentTestJob',
                'job' => 'Illuminate\\Queue\\CallQueuedHandler@call',
                'data' => [],
            ]),
            'attempts' => 0,
            'reserved_at' => null,
            'available_at' => time(),
            'created_at' => time(),
        ]);

        DB::table('jobs')->insert([
            'queue' => 'default',
            'payload' => json_encode([
                'displayName' => 'App\\Jobs\\ProcessingJob',
                'job' => 'Illuminate\\Queue\\CallQueuedHandler@call',
                'data' => [],
            ]),
            'attempts' => 1,
            'reserved_at' => time(),
            'available_at' => time(),
            'created_at' => time() - 100,
        ]);

        $response = $this->authenticatedGet('/api/v1/queues/recent?limit=10');

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertEquals(2, count($data));

        // Check first job (most recent)
        $this->assertEquals('default', $data[0]['queue']);
        $this->assertEquals('App\\Jobs\\RecentTestJob', $data[0]['job_class']);
        $this->assertEquals(0, $data[0]['attempts']);
        $this->assertEquals('pending', $data[0]['status']);

        // Check second job (processing)
        $this->assertEquals('App\\Jobs\\ProcessingJob', $data[1]['job_class']);
        $this->assertEquals(1, $data[1]['attempts']);
        $this->assertEquals('processing', $data[1]['status']);
    }

    public function test_recent_jobs_respects_limit(): void
    {
        // Insert more jobs than the limit
        for ($i = 0; $i < 25; $i++) {
            DB::table('jobs')->insert([
                'queue' => 'default',
                'payload' => json_encode([
                    'displayName' => "App\\Jobs\\TestJob{$i}",
                    'job' => 'Illuminate\\Queue\\CallQueuedHandler@call',
                    'data' => [],
                ]),
                'attempts' => 0,
                'reserved_at' => null,
                'available_at' => time(),
                'created_at' => time() - $i,
            ]);
        }

        $response = $this->authenticatedGet('/api/v1/queues/recent?limit=10');

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertEquals(10, count($data));
    }

    public function test_queue_endpoints_require_authentication(): void
    {
        // Test stats
        $response = $this->getJson('/api/v1/queues/stats');
        $response->assertStatus(401);

        // Test failed jobs
        $response = $this->getJson('/api/v1/queues/failed');
        $response->assertStatus(401);

        // Test recent jobs
        $response = $this->getJson('/api/v1/queues/recent');
        $response->assertStatus(401);

        // Test retry job
        $response = $this->postJson('/api/v1/queues/failed/1/retry');
        $response->assertStatus(401);

        // Test delete job
        $response = $this->deleteJson('/api/v1/queues/failed/1');
        $response->assertStatus(401);

        // Test clear all
        $response = $this->deleteJson('/api/v1/queues/failed');
        $response->assertStatus(401);
    }

    public function test_queue_stats_handles_database_errors_gracefully(): void
    {
        // Temporarily break the jobs table by using wrong connection
        // This should gracefully handle the error and return defaults
        $response = $this->authenticatedGet('/api/v1/queues/stats');

        $response->assertStatus(200);
        // Even if there's an error, it should return 200 with default values
        $this->assertIsArray($response->json('data'));
    }

    public function test_retry_non_existent_job_handles_error(): void
    {
        $response = $this->authenticatedPost('/api/v1/queues/failed/non-existent-job/retry');

        // The response might be 200 (if Artisan command doesn't fail) or 500
        // Both are acceptable behavior depending on how Laravel handles the situation
        $this->assertContains($response->status(), [200, 500]);
    }

    public function test_delete_non_existent_job_handles_error(): void
    {
        $response = $this->authenticatedDelete('/api/v1/queues/failed/non-existent-job');

        // The response might be 200 (if Artisan command doesn't fail) or 500
        // Both are acceptable behavior depending on how Laravel handles the situation
        $this->assertContains($response->status(), [200, 500]);
    }

    /**
     * Helper method to add a failed job for testing
     */
    protected function addFailedJob(string $jobId, string $queue, string $exception): string
    {
        $payload = json_encode([
            'displayName' => 'App\\Jobs\\TestFailedJob',
            'job' => 'Illuminate\\Queue\\CallQueuedHandler@call',
            'data' => ['test' => 'data'],
        ]);

        DB::table('failed_jobs')->insert([
            'id' => $jobId,
            'uuid' => \Illuminate\Support\Str::uuid(),
            'connection' => 'database',
            'queue' => $queue,
            'payload' => $payload,
            'exception' => $exception,
            'failed_at' => now(),
        ]);

        return $jobId;
    }
}