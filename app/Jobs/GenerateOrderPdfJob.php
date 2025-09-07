<?php

namespace App\Jobs;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GenerateOrderPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $timeout = 300; // 5 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Order $order,
        public array $images,
        public int $cellSize,
        public string $overlayUrl
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting PDF generation', [
            'order_id' => $this->order->id,
            'order_number' => $this->order->number,
            'images_count' => count($this->images),
            'cell_size' => $this->cellSize,
        ]);

        try {
            // Create images directory if it doesn't exist
            Storage::disk('local')->makeDirectory('temp_images');

            // Download and process images
            $processedImages = $this->downloadAndProcessImages();

            // Download overlay
            $overlayPath = $this->downloadOverlay();

            // Generate HTML for PDF
            $html = $this->generateHtml($processedImages, $overlayPath);

            // Generate PDF
            $pdfPath = $this->generatePdf($html);

            // Update order with PDF path
            $this->order->update(['pdf_path' => $pdfPath]);

            // Clean up temporary files
            $this->cleanupTempFiles($processedImages, $overlayPath);

            Log::info('PDF generated successfully', [
                'order_id' => $this->order->id,
                'pdf_path' => $pdfPath,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to generate PDF', [
                'order_id' => $this->order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Download and process images from R2
     */
    private function downloadAndProcessImages(): array
    {
        $processedImages = [];

        foreach ($this->images as $index => $imageUrl) {
            Log::info("Processing image {$index}", ['url' => $imageUrl]);

            // Download image
            $response = Http::timeout(60)->get($imageUrl);

            if (!$response->successful()) {
                throw new \Exception("Failed to download image from {$imageUrl}");
            }

            // Save temporary image
            $tempPath = "temp_images/image_{$index}_" . time() . '.png';
            Storage::disk('local')->put($tempPath, $response->body());

            // For simplicity, we'll use the downloaded images directly
            // In a real implementation, you might want to use Intervention Image
            // to resize and validate dimensions
            $fullPath = Storage::disk('local')->path($tempPath);
            $processedImages[] = $fullPath;
        }

        return $processedImages;
    }

    /**
     * Download overlay file
     */
    private function downloadOverlay(): string
    {
        Log::info('Downloading overlay', ['url' => $this->overlayUrl]);

        $response = Http::timeout(60)->get($this->overlayUrl);

        if (!$response->successful()) {
            throw new \Exception("Failed to download overlay from {$this->overlayUrl}");
        }

        $overlayPath = 'temp_images/overlay_' . time() . '.svg';
        Storage::disk('local')->put($overlayPath, $response->body());

        return Storage::disk('local')->path($overlayPath);
    }

    /**
     * Generate HTML template for PDF
     */
    private function generateHtml(array $processedImages, string $overlayPath): string
    {
        // Calculate grid dimensions
        $gridSize = 3;
        $cellsPerRow = $gridSize;
        $totalCells = $gridSize * $gridSize;

        // Fill empty cells if needed
        while (count($processedImages) < $totalCells) {
            $processedImages[] = null;
        }

        // Generate HTML
        $html = view('pdf.order-grid', [
            'images' => $processedImages,
            'overlayPath' => $overlayPath,
            'cellSize' => $this->cellSize,
            'cellsPerRow' => $cellsPerRow,
            'order' => $this->order,
        ])->render();

        return $html;
    }

    /**
     * Generate PDF from HTML
     */
    private function generatePdf(string $html): string
    {
        // Configure PDF options for high quality
        $pdf = Pdf::loadHTML($html)
            ->setPaper('letter', 'portrait')
            ->setOptions([
                'dpi' => 300,
                'defaultFont' => 'sans-serif',
                'isRemoteEnabled' => true,
                'isJavascriptEnabled' => false,
                'isPhpEnabled' => false,
                'margin_top' => 36, // 0.5 inch at 72 DPI
                'margin_bottom' => 36, // 0.5 inch at 72 DPI
                'margin_left' => 0,
                'margin_right' => 0,
            ]);

        // Ensure PDF directory exists
        Storage::disk('pdfs')->makeDirectory('');

        // Generate filename
        $filename = "order_{$this->order->id}.pdf";
        $pdfPath = Storage::disk('pdfs')->path($filename);

        // Save PDF
        $pdf->save($pdfPath);

        return $filename;
    }

    /**
     * Clean up temporary files
     */
    private function cleanupTempFiles(array $processedImages, string $overlayPath): void
    {
        // Clean up processed images
        foreach ($processedImages as $imagePath) {
            if ($imagePath && file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        // Clean up overlay
        if (file_exists($overlayPath)) {
            unlink($overlayPath);
        }

        // Clean up temp directory if empty
        $tempDir = Storage::disk('local')->path('temp_images');
        if (is_dir($tempDir) && count(scandir($tempDir)) === 2) { // Only . and ..
            rmdir($tempDir);
        }
    }
}
