<?php

namespace App\Http\Controllers\Spa;

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
            $perPage = min($request->get('per_page', 20), 100);
            $page = $request->get('page', 1);

            $failedJobs = collect($this->failedJobProvider->all())
                ->map(function ($job) {
                    return [
                        'id' => $job->id,
                        'queue' => $job->queue,
                        'payload' => json_decode($job->payload, true),
                        'exception' => $job->exception,
                        'failed_at' => $job->failed_at,
                    ];
                });

            $total = $failedJobs->count();
            $items = $failedJobs->skip(($page - 1) * $perPage)->take($perPage)->values();

            return response()->json([
                'data' => $items,
                'current_page' => $page,
                'last_page' => ceil($total / $perPage),
                'per_page' => $perPage,
                'total' => $total,
                'from' => $total > 0 ? (($page - 1) * $perPage) + 1 : null,
                'to' => $total > 0 ? min($page * $perPage, $total) : null,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve failed jobs',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get recent jobs (completed in the last 24 hours).
     */
    public function recentJobs(Request $request): JsonResponse
    {
        try {
            // Since Laravel doesn't track completed jobs by default,
            // we'll return jobs from the jobs table if using database queue
            $recentJobs = [];

            if (config('queue.default') === 'database') {
                $recentJobs = DB::table('jobs')
                    ->where('created_at', '>=', now()->subDay())
                    ->orderBy('created_at', 'desc')
                    ->limit(50)
                    ->get()
                    ->map(function ($job) {
                        return [
                            'id' => $job->id,
                            'queue' => $job->queue,
                            'payload' => json_decode($job->payload, true),
                            'attempts' => $job->attempts,
                            'created_at' => $job->created_at,
                            'available_at' => $job->available_at,
                        ];
                    });
            }

            return response()->json([
                'data' => $recentJobs,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve recent jobs',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Retry a failed job.
     */
    public function retryJob(string $jobId): JsonResponse
    {
        try {
            $exitCode = Artisan::call('queue:retry', ['id' => $jobId]);

            if ($exitCode === 0) {
                return response()->json([
                    'message' => 'Job retry initiated successfully',
                ]);
            } else {
                return response()->json([
                    'error' => 'Failed to retry job',
                    'message' => 'Artisan command failed',
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
                    'message' => 'Failed job deleted successfully',
                ]);
            } else {
                return response()->json([
                    'error' => 'Failed to delete job',
                    'message' => 'Artisan command failed',
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
     * Get the number of pending jobs.
     */
    private function getPendingJobsCount(): int
    {
        try {
            if (config('queue.default') === 'database') {
                return DB::table('jobs')->count();
            }

            return 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get the number of failed jobs.
     */
    private function getFailedJobsCount(): int
    {
        try {
            return count($this->failedJobProvider->all());
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get the number of jobs processed today.
     */
    private function getProcessedJobsToday(): int
    {
        // This would require custom tracking or a job events listener
        // For now, return a cached value or 0
        return Cache::get('jobs_processed_today', 0);
    }

    /**
     * Get available queue names.
     */
    private function getQueueNames(): array
    {
        try {
            if (config('queue.default') === 'database') {
                return DB::table('jobs')
                    ->distinct()
                    ->pluck('queue')
                    ->toArray();
            }

            return ['default'];
        } catch (\Exception $e) {
            return ['default'];
        }
    }

    /**
     * Get workers status (simplified).
     */
    private function getWorkersStatus(): array
    {
        // This would require horizon or custom worker tracking
        // For now, return basic status
        return [
            'active' => 1,
            'total' => 1,
            'paused' => false,
        ];
    }
}
