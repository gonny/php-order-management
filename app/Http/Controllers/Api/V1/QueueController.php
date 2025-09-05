<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Queue\Failed\FailedJobProviderInterface;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class QueueController extends Controller
{
    public function __construct(
        private FailedJobProviderInterface $failedJobProvider
    ) {}

    /**
     * Get queue statistics and metrics.
     */
    public function stats(): JsonResponse
    {
        try {
            // Get queue statistics
            $queueStats = [
                'pending_jobs' => $this->getPendingJobsCount(),
                'failed_jobs' => $this->getFailedJobsCount(),
                'processed_jobs_today' => $this->getProcessedJobsToday(),
                'queue_names' => $this->getQueueNames(),
                'workers_status' => $this->getWorkersStatus(),
            ];

            return response()->json([
                'data' => $queueStats,
                'message' => 'Queue statistics retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve queue statistics',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get failed jobs with pagination.
     */
    public function failedJobs(Request $request): JsonResponse
    {
        try {
            $page = $request->get('page', 1);
            $perPage = min($request->get('per_page', 15), 100);
            
            $failedJobs = collect($this->failedJobProvider->all())
                ->map(function ($job) {
                    return [
                        'id' => $job->id,
                        'uuid' => $job->uuid ?? null,
                        'connection' => $job->connection,
                        'queue' => $job->queue,
                        'payload' => json_decode($job->payload, true),
                        'exception' => $job->exception,
                        'failed_at' => $job->failed_at,
                    ];
                })
                ->sortByDesc('failed_at')
                ->values();

            $total = $failedJobs->count();
            $paginatedJobs = $failedJobs->slice(($page - 1) * $perPage, $perPage)->values();

            return response()->json([
                'data' => $paginatedJobs,
                'meta' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'last_page' => ceil($total / $perPage),
                ],
                'message' => 'Failed jobs retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve failed jobs',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Retry a failed job.
     */
    public function retryJob(Request $request, string $jobId): JsonResponse
    {
        try {
            $exitCode = Artisan::call('queue:retry', ['id' => [$jobId]]);
            
            if ($exitCode === 0) {
                return response()->json([
                    'message' => 'Job queued for retry successfully'
                ]);
            } else {
                return response()->json([
                    'error' => 'Failed to retry job',
                    'message' => 'Artisan command failed with exit code: ' . $exitCode,
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to retry job',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a failed job.
     */
    public function deleteFailedJob(string $jobId): JsonResponse
    {
        try {
            $exitCode = Artisan::call('queue:forget', ['id' => $jobId]);
            
            if ($exitCode === 0) {
                return response()->json([
                    'message' => 'Failed job deleted successfully'
                ]);
            } else {
                return response()->json([
                    'error' => 'Failed to delete job',
                    'message' => 'Artisan command failed with exit code: ' . $exitCode,
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete job',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear all failed jobs.
     */
    public function clearFailedJobs(): JsonResponse
    {
        try {
            $exitCode = Artisan::call('queue:flush');
            
            if ($exitCode === 0) {
                return response()->json([
                    'message' => 'All failed jobs cleared successfully'
                ]);
            } else {
                return response()->json([
                    'error' => 'Failed to clear all jobs',
                    'message' => 'Artisan command failed with exit code: ' . $exitCode,
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to clear all jobs',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get recent jobs with their status.
     */
    public function recentJobs(Request $request): JsonResponse
    {
        try {
            $limit = min($request->get('limit', 20), 100);
            
            // This is a basic implementation - in production you might want to use
            // a more sophisticated job tracking system like Laravel Horizon
            $recentJobs = DB::table('jobs')
                ->select(['id', 'queue', 'payload', 'attempts', 'reserved_at', 'available_at', 'created_at'])
                ->latest('created_at')
                ->limit($limit)
                ->get()
                ->map(function ($job) {
                    $payload = json_decode($job->payload, true);
                    return [
                        'id' => $job->id,
                        'queue' => $job->queue,
                        'job_class' => $payload['displayName'] ?? 'Unknown',
                        'attempts' => $job->attempts,
                        'status' => $job->reserved_at ? 'processing' : 'pending',
                        'created_at' => $job->created_at,
                    ];
                });

            return response()->json([
                'data' => $recentJobs,
                'message' => 'Recent jobs retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve recent jobs',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    private function getPendingJobsCount(): int
    {
        try {
            return DB::table('jobs')->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getFailedJobsCount(): int
    {
        try {
            return count($this->failedJobProvider->all());
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getProcessedJobsToday(): int
    {
        // This would require custom job tracking - simplified for now
        return Cache::get('jobs_processed_today', 0);
    }

    private function getQueueNames(): array
    {
        try {
            $queues = DB::table('jobs')
                ->distinct()
                ->pluck('queue')
                ->toArray();
            
            return array_merge(['default'], $queues);
        } catch (\Exception $e) {
            return ['default'];
        }
    }

    private function getWorkersStatus(): array
    {
        // This is a simplified implementation - in production you'd want more
        // sophisticated worker monitoring
        return [
            'active_workers' => 1, // Would need to check actual worker processes
            'last_heartbeat' => now()->toISOString(),
        ];
    }
}