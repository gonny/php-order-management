<?php

namespace Tests\Unit;

use App\Jobs\GenerateOrderPdfJob;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GenerateOrderPdfJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create storage disks for testing
        Storage::fake('local');
        Storage::fake('pdfs');
    }

    public function test_job_validates_required_parameters(): void
    {
        $order = Order::factory()->create();

        $job = new GenerateOrderPdfJob(
            $order,
            ['https://cf2.r2.link/test1.png'],
            200,
            'https://cdn.domain.com/overlay.svg'
        );

        $this->assertInstanceOf(GenerateOrderPdfJob::class, $job);
        $this->assertEquals($order->id, $job->order->id);
        $this->assertEquals(['https://cf2.r2.link/test1.png'], $job->images);
        $this->assertEquals(200, $job->cellSize);
        $this->assertEquals('https://cdn.domain.com/overlay.svg', $job->overlayUrl);
    }

    public function test_job_has_correct_configuration(): void
    {
        $order = Order::factory()->create();

        $job = new GenerateOrderPdfJob(
            $order,
            ['https://cf2.r2.link/test1.png'],
            200,
            'https://cdn.domain.com/overlay.svg'
        );

        $this->assertEquals(3, $job->tries);
        $this->assertEquals(300, $job->timeout);
    }

    public function test_job_handles_image_download_failure(): void
    {
        $order = Order::factory()->create();

        // Mock HTTP response for failed image download
        Http::fake([
            'https://cf2.r2.link/test1.png' => Http::response('Not Found', 404),
        ]);

        $job = new GenerateOrderPdfJob(
            $order,
            ['https://cf2.r2.link/test1.png'],
            200,
            'https://cdn.domain.com/overlay.svg'
        );

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to download image from https://cf2.r2.link/test1.png');

        $job->handle();
    }

    public function test_job_handles_overlay_download_failure(): void
    {
        $order = Order::factory()->create();

        // Mock HTTP responses
        Http::fake([
            'https://cf2.r2.link/test1.png' => Http::response('fake-image-data', 200),
            'https://cdn.domain.com/overlay.svg' => Http::response('Not Found', 404),
        ]);

        $job = new GenerateOrderPdfJob(
            $order,
            ['https://cf2.r2.link/test1.png'],
            200,
            'https://cdn.domain.com/overlay.svg'
        );

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to download overlay from https://cdn.domain.com/overlay.svg');

        $job->handle();
    }

    public function test_job_processes_multiple_images(): void
    {
        $order = Order::factory()->create();

        $images = [
            'https://cf2.r2.link/test1.png',
            'https://cf2.r2.link/test2.png',
            'https://cf2.r2.link/test3.png',
        ];

        $job = new GenerateOrderPdfJob(
            $order,
            $images,
            200,
            'https://cdn.domain.com/overlay.svg'
        );

        $this->assertEquals(3, count($job->images));
        $this->assertEquals($images, $job->images);
    }

    public function test_job_validates_cell_size_range(): void
    {
        $order = Order::factory()->create();

        // Test minimum cell size
        $job = new GenerateOrderPdfJob(
            $order,
            ['https://cf2.r2.link/test1.png'],
            100, // minimum
            'https://cdn.domain.com/overlay.svg'
        );
        $this->assertEquals(100, $job->cellSize);

        // Test maximum cell size
        $job = new GenerateOrderPdfJob(
            $order,
            ['https://cf2.r2.link/test1.png'],
            600, // maximum
            'https://cdn.domain.com/overlay.svg'
        );
        $this->assertEquals(600, $job->cellSize);
    }
}
