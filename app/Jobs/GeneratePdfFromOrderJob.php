<?php

namespace App\Jobs;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class GeneratePdfFromOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 300; // 5 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Order $order
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting PDF generation from local photos', [
            'order_id' => $this->order->id,
            'remote_session_id' => $this->order->remote_session_id,
            'photo_paths_count' => count($this->order->local_photo_paths ?? []),
        ]);

        try {
            // Validate that we have local photo paths
            if (empty($this->order->local_photo_paths)) {
                throw new Exception('No local photo paths found for order');
            }

            // Validate that all photos exist
            $this->validateLocalPhotos();

            // Generate HTML for PDF
            $html = $this->generateHtml();

            // Generate PDF and save it in the same directory as photos
            $pdfPath = $this->generatePdf($html);

            // Update order with PDF path
            $this->order->update(['pdf_path' => $pdfPath]);

            Log::info('PDF generated successfully from local photos', [
                'order_id' => $this->order->id,
                'pdf_path' => $pdfPath,
            ]);

        } catch (Exception $e) {
            Log::error('Failed to generate PDF from local photos', [
                'order_id' => $this->order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Validate that all local photos exist
     */
    private function validateLocalPhotos(): void
    {
        $uploadsDisk = Storage::disk('uploads');
        
        foreach ($this->order->local_photo_paths as $photoPath) {
            if (!$uploadsDisk->exists($photoPath)) {
                throw new Exception("Local photo not found: {$photoPath}");
            }
        }
    }

    /**
     * Generate HTML template for PDF
     */
    private function generateHtml(): string
    {
        // Get full paths to local photos
        $uploadsDisk = Storage::disk('uploads');
        $processedImages = [];
        
        foreach ($this->order->local_photo_paths as $photoPath) {
            $fullPath = $uploadsDisk->path($photoPath);
            $processedImages[] = $fullPath;
        }

        // Calculate grid dimensions - ensure we have exactly 9 photos
        $gridSize = 3;
        $cellsPerRow = $gridSize;
        $totalCells = $gridSize * $gridSize;

        // Fill empty cells if needed or trim excess
        while (count($processedImages) < $totalCells) {
            $processedImages[] = null;
        }
        $processedImages = array_slice($processedImages, 0, $totalCells);

        // Generate HTML - using a simple template for now
        $html = view('pdf.order-grid-r2', [
            'images' => $processedImages,
            'cellSize' => 200, // Default cell size
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
                'margin_top' => 36,
                'margin_bottom' => 36,
                'margin_left' => 0,
                'margin_right' => 0,
            ]);

        // Save PDF in the same directory as the photos
        $sessionDirectory = "uploads/{$this->order->remote_session_id}";
        Storage::disk('local')->makeDirectory($sessionDirectory);
        
        $filename = "order_{$this->order->id}.pdf";
        $pdfPath = "{$sessionDirectory}/{$filename}";
        $fullPdfPath = Storage::disk('local')->path($pdfPath);

        // Save PDF
        $pdf->save($fullPdfPath);

        return $pdfPath;
    }

    /**
     * Handle job failure
     */
    public function failed(Exception $exception): void
    {
        Log::error('GeneratePdfFromOrderJob failed', [
            'order_id' => $this->order->id,
            'remote_session_id' => $this->order->remote_session_id,
            'exception' => $exception->getMessage(),
        ]);
    }
}
