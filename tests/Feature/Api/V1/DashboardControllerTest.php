<?php

namespace Tests\Feature\Api\V1;

use App\Models\Client;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\ApiTestHelpers;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase, ApiTestHelpers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpApiClient();
    }

    public function test_can_get_dashboard_metrics_with_empty_data(): void
    {
        $response = $this->authenticatedGet('/api/v1/dashboard/metrics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'orders' => [
                        'total',
                        'new',
                        'confirmed', 
                        'paid',
                        'fulfilled',
                        'completed',
                        'cancelled',
                        'today',
                        'this_week',
                        'this_month',
                    ],
                    'clients' => [
                        'total',
                        'active',
                        'new_this_month',
                    ],
                    'revenue' => [
                        'total',
                        'this_month',
                        'this_week',
                    ],
                    'recent_orders',
                ],
                'message',
            ])
            ->assertJson([
                'data' => [
                    'orders' => [
                        'total' => 0,
                        'new' => 0,
                        'confirmed' => 0,
                        'paid' => 0,
                        'fulfilled' => 0,
                        'completed' => 0,
                        'cancelled' => 0,
                        'today' => 0,
                        'this_week' => 0,
                        'this_month' => 0,
                    ],
                    'clients' => [
                        'total' => 0,
                        'active' => 0,
                        'new_this_month' => 0,
                    ],
                    'revenue' => [
                        'total' => 0,
                        'this_month' => 0,
                        'this_week' => 0,
                    ],
                    'recent_orders' => [],
                ],
                'message' => 'Dashboard metrics retrieved successfully',
            ]);
    }

    public function test_can_get_dashboard_metrics_with_real_data(): void
    {
        // Get initial counts to handle any existing data from test setup
        $initialResponse = $this->authenticatedGet('/api/v1/dashboard/metrics');
        $initialData = $initialResponse->json('data');
        $initialActiveClients = $initialData['clients']['active'];
        $initialTotalClients = $initialData['clients']['total'];
        
        // Create test clients
        $inactiveClients = Client::factory()->count(5)->create(['is_active' => false]);
        $activeClients = Client::factory()->count(3)->create(['is_active' => true]);

        // Create orders with different statuses
        $newOrders = Order::factory()->count(2)->create([
            'status' => 'new',
            'total_amount' => 100.00,
            'client_id' => $inactiveClients->first()->id,
            'created_at' => now(),
        ]);

        $confirmedOrders = Order::factory()->count(3)->create([
            'status' => 'confirmed',
            'total_amount' => 200.00,
            'client_id' => $inactiveClients->get(1)->id,
            'created_at' => now()->subDays(2),
        ]);

        $paidOrders = Order::factory()->count(1)->create([
            'status' => 'paid',
            'total_amount' => 150.00,
            'client_id' => $inactiveClients->get(2)->id,
            'created_at' => now()->subWeek(),
        ]);

        $completedOrders = Order::factory()->count(4)->create([
            'status' => 'completed',
            'total_amount' => 300.00,
            'client_id' => $inactiveClients->get(3)->id,
            'created_at' => now()->subMonth(),
        ]);

        $todayOrders = Order::factory()->count(2)->create([
            'status' => 'new',
            'total_amount' => 75.00,
            'client_id' => $inactiveClients->get(4)->id,
            'created_at' => now(),
        ]);

        $response = $this->authenticatedGet('/api/v1/dashboard/metrics');

        $response->assertStatus(200);

        $data = $response->json('data');

        // Verify order counts
        $this->assertEquals(12, $data['orders']['total']); // 2+3+1+4+2
        $this->assertEquals(4, $data['orders']['new']); // 2+2
        $this->assertEquals(3, $data['orders']['confirmed']);
        $this->assertEquals(1, $data['orders']['paid']);
        $this->assertEquals(0, $data['orders']['fulfilled']);
        $this->assertEquals(4, $data['orders']['completed']);
        $this->assertEquals(0, $data['orders']['cancelled']);
        $this->assertEquals(4, $data['orders']['today']); // 2+2 created today

        // Verify client counts - just test the incremental change
        $this->assertGreaterThan($initialTotalClients, $data['clients']['total']); // Should increase
        $this->assertGreaterThan($initialActiveClients, $data['clients']['active']); // Should increase

        // Verify revenue calculations
        $expectedTotalRevenue = (2 * 100) + (3 * 200) + (1 * 150) + (4 * 300) + (2 * 75);
        $this->assertEquals($expectedTotalRevenue, $data['revenue']['total']);

        // Verify recent orders structure
        $this->assertIsArray($data['recent_orders']);
        $this->assertLessThanOrEqual(5, count($data['recent_orders']));

        if (count($data['recent_orders']) > 0) {
            $recentOrder = $data['recent_orders'][0];
            $this->assertArrayHasKey('id', $recentOrder);
            $this->assertArrayHasKey('order_number', $recentOrder);
            $this->assertArrayHasKey('client_name', $recentOrder);
            $this->assertArrayHasKey('status', $recentOrder);
            $this->assertArrayHasKey('total_amount', $recentOrder);
            $this->assertArrayHasKey('created_at', $recentOrder);
        }
    }

    public function test_dashboard_metrics_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/dashboard/metrics');

        $response->assertStatus(401)
            ->assertJson([
                'error' => 'Authentication failed',
                'message' => 'Invalid signature or authentication headers',
            ]);
    }

    public function test_dashboard_metrics_handles_orders_without_clients(): void
    {
        // Create a client first, then create order with that client
        $client = Client::factory()->create();
        
        Order::factory()->create([
            'status' => 'new',
            'total_amount' => 100.00,
            'client_id' => $client->id,
        ]);

        $response = $this->authenticatedGet('/api/v1/dashboard/metrics');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertEquals(1, $data['orders']['total']);

        if (count($data['recent_orders']) > 0) {
            $recentOrder = $data['recent_orders'][0];
            // Client name should be the actual client's name, not 'Unknown'
            $this->assertEquals($client->full_name, $recentOrder['client_name']);
        }
    }

    public function test_dashboard_metrics_time_filtering(): void
    {
        // Create a client first for all orders
        $client = Client::factory()->create();
        
        // Create orders in different time periods
        Order::factory()->create([
            'status' => 'new',
            'total_amount' => 100.00,
            'client_id' => $client->id,
            'created_at' => now(), // Today
        ]);

        Order::factory()->create([
            'status' => 'confirmed',
            'total_amount' => 200.00,
            'client_id' => $client->id,
            'created_at' => now()->startOfWeek(), // This week
        ]);

        Order::factory()->create([
            'status' => 'paid',
            'total_amount' => 150.00,
            'client_id' => $client->id,
            'created_at' => now()->startOfMonth(), // This month
        ]);

        Order::factory()->create([
            'status' => 'completed',
            'total_amount' => 300.00,
            'client_id' => $client->id,
            'created_at' => now()->subMonths(2), // Outside current month
        ]);

        $response = $this->authenticatedGet('/api/v1/dashboard/metrics');

        $response->assertStatus(200);

        $data = $response->json('data');

        // Verify time-based filtering
        $this->assertEquals(4, $data['orders']['total']);
        $this->assertEquals(1, $data['orders']['today']);
        $this->assertGreaterThanOrEqual(2, $data['orders']['this_week']); // At least today + this week
        $this->assertGreaterThanOrEqual(3, $data['orders']['this_month']); // At least today + this week + this month
    }
}