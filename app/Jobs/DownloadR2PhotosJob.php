<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class DownloadR2PhotosJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 600; // 10 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Order $order,
        public array $r2PhotoLinks,
        public string $remoteSessionId
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting R2 photo download', [
            'order_id' => $this->order->id,
            'remote_session_id' => $this->remoteSessionId,
            'photos_count' => count($this->r2PhotoLinks),
        ]);

        try {
            // Create session directory if it doesn't exist
            $sessionDirectory = "uploads/{$this->remoteSessionId}";
            Storage::disk('uploads')->makeDirectory($sessionDirectory);

            $localPhotoPaths = [];

            foreach ($this->r2PhotoLinks as $index => $r2PhotoLink) {
                Log::info("Downloading photo {$index}", [
                    'r2_link' => $r2PhotoLink,
                    'order_id' => $this->order->id,
                ]);

                $localPath = $this->downloadPhotoFromR2($r2PhotoLink, $sessionDirectory, $index);
                $localPhotoPaths[] = $localPath;
            }

            // Update order with downloaded photo paths
            $this->order->update([
                'remote_session_id' => $this->remoteSessionId,
                'r2_photo_links' => $this->r2PhotoLinks,
                'local_photo_paths' => $localPhotoPaths,
            ]);

            // Chain the PDF generation job
            GeneratePdfFromOrderJob::dispatch($this->order);

            Log::info('R2 photos downloaded successfully', [
                'order_id' => $this->order->id,
                'photos_downloaded' => count($localPhotoPaths),
                'local_paths' => $localPhotoPaths,
            ]);

        } catch (Exception $e) {
            Log::error('Failed to download R2 photos', [
                'order_id' => $this->order->id,
                'remote_session_id' => $this->remoteSessionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Download photo from R2 to local storage
     */
    private function downloadPhotoFromR2(string $r2PhotoLink, string $sessionDirectory, int $index): string
    {
        try {
            // Extract photo name from the R2 link or generate one
            $photoName = $this->extractPhotoNameFromLink($r2PhotoLink, $index);
            $localPath = "{$sessionDirectory}/{$photoName}";

            // Download from R2
            $r2Disk = Storage::disk('r2');
            
            // Extract the key from the R2 URL - this assumes R2 URLs follow a pattern
            $r2Key = $this->extractR2KeyFromUrl($r2PhotoLink);
            
            if (!$r2Disk->exists($r2Key)) {
                throw new Exception("Photo not found in R2: {$r2Key}");
            }

            // Copy from R2 to local uploads disk
            $photoContent = $r2Disk->get($r2Key);
            Storage::disk('uploads')->put($localPath, $photoContent);

            Log::info('Photo downloaded successfully', [
                'r2_key' => $r2Key,
                'local_path' => $localPath,
                'size' => strlen($photoContent),
            ]);

            return $localPath;

        } catch (Exception $e) {
            Log::error('Failed to download individual photo', [
                'r2_link' => $r2PhotoLink,
                'index' => $index,
                'error' => $e->getMessage(),
            ]);

            throw new Exception("Failed to download photo {$index} from R2: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Extract photo name from R2 link
     */
    private function extractPhotoNameFromLink(string $r2PhotoLink, int $index): string
    {
        // Extract filename from URL
        $path = parse_url($r2PhotoLink, PHP_URL_PATH);
        $filename = basename($path);
        
        // If no extension found, default to .png
        if (!pathinfo($filename, PATHINFO_EXTENSION)) {
            $filename = "photo_{$index}.png";
        }

        return $filename;
    }

    /**
     * Extract R2 key from URL
     */
    private function extractR2KeyFromUrl(string $url): string
    {
        // For Cloudflare R2, extract the key from the URL
        // This assumes URLs like: https://{account_id}.r2.cloudflarestorage.com/{bucket}/{key}
        $parsed = parse_url($url);
        $path = ltrim($parsed['path'], '/');
        
        // Remove bucket name from path if present
        $pathParts = explode('/', $path, 2);
        
        if (count($pathParts) > 1) {
            return $pathParts[1]; // Return everything after the first slash (assuming first part is bucket)
        }
        
        return $path;
    }

    /**
     * Handle job failure
     */
    public function failed(Exception $exception): void
    {
        Log::error('DownloadR2PhotosJob failed', [
            'order_id' => $this->order->id,
            'remote_session_id' => $this->remoteSessionId,
            'exception' => $exception->getMessage(),
        ]);
    }
}
