<?php

namespace Tests\Feature;

use App\Jobs\GenerateOrderPdfJob;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PdfGenerationIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Fake storage for testing
        Storage::fake('local');
        Storage::fake('pdfs');
    }

    public function test_pdf_generation_job_runs_successfully(): void
    {
        $order = Order::factory()->create();

        // Mock HTTP responses for image and overlay downloads
        Http::fake([
            'https://cf2.r2.link/test1.png' => Http::response('fake-image-data', 200),
            'https://cf2.r2.link/test2.png' => Http::response('fake-image-data', 200),
            'https://cdn.domain.com/overlay.svg' => Http::response('<svg>fake-overlay</svg>', 200),
        ]);

        $job = new GenerateOrderPdfJob(
            $order,
            [
                'https://cf2.r2.link/test1.png',
                'https://cf2.r2.link/test2.png',
            ],
            200,
            'https://cdn.domain.com/overlay.svg'
        );

        // Execute the job
        $job->handle();

        // Verify the order was updated with PDF path
        $order->refresh();
        $this->assertNotNull($order->pdf_path);
        $this->assertStringStartsWith('order_', $order->pdf_path);
        $this->assertStringEndsWith('.pdf', $order->pdf_path);

        // Verify PDF file was created
        Storage::disk('pdfs')->assertExists($order->pdf_path);
    }

    public function test_pdf_generation_handles_job_parameters_correctly(): void
    {
        $order = Order::factory()->create();

        Http::fake([
            '*' => Http::response('fake-content', 200),
        ]);

        // Test with different cell sizes and image counts
        $testCases = [
            ['images' => 1, 'cell_size' => 100],
            ['images' => 5, 'cell_size' => 300],
            ['images' => 9, 'cell_size' => 600],
        ];

        foreach ($testCases as $index => $case) {
            $images = array_fill(0, $case['images'], "https://cf2.r2.link/test{$index}.png");

            $job = new GenerateOrderPdfJob(
                $order,
                $images,
                $case['cell_size'],
                'https://cdn.domain.com/overlay.svg'
            );

            $this->assertEquals($case['images'], count($job->images));
            $this->assertEquals($case['cell_size'], $job->cellSize);
            $this->assertInstanceOf(Order::class, $job->order);
        }
    }

    public function test_pdf_storage_configuration_works(): void
    {
        // Test that the pdfs disk is properly configured
        $this->assertNotNull(config('filesystems.disks.pdfs'));

        // Test we can write to the pdfs disk
        Storage::disk('pdfs')->put('test.pdf', 'test content');
        Storage::disk('pdfs')->assertExists('test.pdf');

        // Configuration verified by successful disk operations above
    }
}
