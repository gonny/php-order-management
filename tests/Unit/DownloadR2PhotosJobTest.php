<?php

namespace Tests\Unit;

use App\Jobs\DownloadR2PhotosJob;
use App\Jobs\GeneratePdfFromOrderJob;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DownloadR2PhotosJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up storage disks for testing
        Storage::fake('r2');
        Storage::fake('uploads');
        Storage::fake('local');
    }

    public function test_job_validates_required_parameters(): void
    {
        $order = Order::factory()->create();
        $r2PhotoLinks = ['https://example.com/photo1.jpg'];
        $remoteSessionId = 'test-session-123';

        $job = new DownloadR2PhotosJob($order, $r2PhotoLinks, $remoteSessionId);

        $this->assertInstanceOf(DownloadR2PhotosJob::class, $job);
        $this->assertEquals($order->id, $job->order->id);
        $this->assertEquals($r2PhotoLinks, $job->r2PhotoLinks);
        $this->assertEquals($remoteSessionId, $job->remoteSessionId);
    }

    public function test_job_has_correct_configuration(): void
    {
        $order = Order::factory()->create();
        $job = new DownloadR2PhotosJob($order, [], 'test-session');

        $this->assertEquals(3, $job->tries);
        $this->assertEquals(600, $job->timeout);
    }

    public function test_job_creates_session_directory(): void
    {
        $order = Order::factory()->create();
        $remoteSessionId = 'test-session-123';
        $r2PhotoLinks = [];

        Storage::disk('uploads')->assertMissing("uploads/{$remoteSessionId}");

        $job = new DownloadR2PhotosJob($order, $r2PhotoLinks, $remoteSessionId);

        // Mock empty R2 links to test directory creation without actual file operations
        $this->expectException(\Exception::class);
        $job->handle();

        // Directory should be created even if job fails
        Storage::disk('uploads')->assertExists("uploads/{$remoteSessionId}");
    }

    public function test_job_handles_download_failure(): void
    {
        $order = Order::factory()->create();
        $r2PhotoLinks = ['https://r2.example.com/nonexistent.jpg'];
        $remoteSessionId = 'test-session-123';

        $job = new DownloadR2PhotosJob($order, $r2PhotoLinks, $remoteSessionId);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Photo not found in R2');

        $job->handle();
    }

    public function test_job_chains_pdf_generation(): void
    {
        Queue::fake();

        $order = Order::factory()->create();
        $remoteSessionId = 'test-session-123';
        $r2PhotoLinks = ['https://r2.example.com/photo1.jpg'];

        // Mock a successful photo in R2
        Storage::disk('r2')->put('photo1.jpg', 'fake-image-content');

        $job = new DownloadR2PhotosJob($order, $r2PhotoLinks, $remoteSessionId);
        $job->handle();

        // Verify PDF generation job was dispatched
        Queue::assertPushed(GeneratePdfFromOrderJob::class, function ($job) use ($order) {
            return $job->order->id === $order->id;
        });
    }

    public function test_job_extracts_photo_name_from_link(): void
    {
        $order = Order::factory()->create();
        $job = new DownloadR2PhotosJob($order, [], 'test-session');

        $reflection = new \ReflectionClass($job);
        $method = $reflection->getMethod('extractPhotoNameFromLink');
        $method->setAccessible(true);

        $result1 = $method->invokeArgs($job, ['https://example.com/path/photo.jpg', 0]);
        $this->assertEquals('photo.jpg', $result1);

        $result2 = $method->invokeArgs($job, ['https://example.com/path/without-extension', 0]);
        $this->assertEquals('photo_0.png', $result2);
    }

    public function test_job_extracts_r2_key_from_url(): void
    {
        $order = Order::factory()->create();
        $job = new DownloadR2PhotosJob($order, [], 'test-session');

        $reflection = new \ReflectionClass($job);
        $method = $reflection->getMethod('extractR2KeyFromUrl');
        $method->setAccessible(true);

        $result1 = $method->invokeArgs($job, ['https://account.r2.cloudflarestorage.com/bucket/path/photo.jpg']);
        $this->assertEquals('path/photo.jpg', $result1);

        $result2 = $method->invokeArgs($job, ['https://account.r2.cloudflarestorage.com/photo.jpg']);
        $this->assertEquals('photo.jpg', $result2);
    }

    public function test_job_updates_order_with_downloaded_paths(): void
    {
        $order = Order::factory()->create();
        $remoteSessionId = 'test-session-123';
        $r2PhotoLinks = ['https://r2.example.com/photo1.jpg'];

        // Mock a successful photo in R2
        Storage::disk('r2')->put('photo1.jpg', 'fake-image-content');

        $job = new DownloadR2PhotosJob($order, $r2PhotoLinks, $remoteSessionId);
        $job->handle();

        $order->refresh();

        $this->assertEquals($remoteSessionId, $order->remote_session_id);
        $this->assertEquals($r2PhotoLinks, $order->r2_photo_links);
        $this->assertNotEmpty($order->local_photo_paths);
        $this->assertCount(1, $order->local_photo_paths);
    }
}
