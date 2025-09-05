<?php

namespace Tests\Feature\Web;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QueueWebControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_guests_cannot_access_queue_pages(): void
    {
        $this->get('/queues')->assertRedirect('/login');
        $this->get('/queues/failed')->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_queues_index(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/queues');

        $response->assertStatus(200);
        $response->assertInertia(function ($page) {
            $page->component('queues/Index');
        });
    }

    public function test_authenticated_user_can_view_failed_jobs_page(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/queues/failed');

        $response->assertStatus(200);
        $response->assertInertia(function ($page) {
            $page->component('queues/Failed');
        });
    }
}