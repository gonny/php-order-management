<?php

namespace App\Console\Commands;

use App\Jobs\GenerateOrderPdfJob;
use App\Jobs\GenerateShippingLabel;
use App\Jobs\ProcessOrderStateChange;
use App\Models\Address;
use App\Models\ApiClient;
use App\Models\Client;
use App\Models\Order;
use Database\Factories\OrderItemFactory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class QueueTestDataSeeder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:seed-test-data 
                            {--clients=5 : Number of test clients to create}
                            {--orders=10 : Number of test orders to create}
                            {--jobs=5 : Number of test queue jobs to dispatch}
                            {--api-clients=3 : Number of API clients for testing}
                            {--clear : Clear existing test data before seeding}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed test data for queue testing suite including clients, orders, jobs, and API clients';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Environment safety check
        if (app()->environment('production')) {
            $this->error('This command should not be run in production!');
            return Command::FAILURE;
        }

        if ($this->option('clear')) {
            $this->clearTestData();
        }

        $this->info('Seeding test data for queue testing suite...');

        try {
            DB::transaction(function () {
                // Create API clients for testing
                $this->createApiClients();

                // Create test clients
                $this->createTestClients();

                // Create test orders
                $this->createTestOrders();

                // Dispatch test jobs
                $this->dispatchTestJobs();
            });

            $this->info('Test data seeding completed successfully!');
            $this->showSummary();

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Test data seeding failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Clear existing test data
     */
    private function clearTestData(): void
    {
        $this->warn('Clearing existing test data...');
        
        DB::transaction(function () {
            // Clear queue jobs (only if in development)
            if (app()->environment(['local', 'testing'])) {
                DB::table('jobs')->delete();
                DB::table('failed_jobs')->delete();
            }
            
            // Clear test orders and clients (only those created for testing)
            Order::where('number', 'LIKE', 'TEST-%')->delete();
            Client::where('external_id', 'LIKE', 'TEST_CLIENT_%')->delete();
            ApiClient::where('name', 'LIKE', '%Test API Client%')->delete();
        });
        
        $this->info('Test data cleared.');
    }

    /**
     * Create API clients for testing
     */
    private function createApiClients(): void
    {
        $count = $this->option('api-clients');
        $this->info("Creating {$count} API clients for testing...");

        // Get the highest existing test API client number to avoid conflicts
        $existingCount = ApiClient::where('name', 'LIKE', '%Test API Client%')->count();
        $startNumber = $existingCount + 1;

        for ($i = 0; $i < $count; $i++) {
            $clientNumber = $startNumber + $i;
            $keyId = 'test_' . Str::random(16);
            $secret = Str::random(64); // Generate secret
            $secretHash = hash('sha256', $secret); // Hash for storage

            ApiClient::create([
                'name' => "Test API Client {$clientNumber}",
                'key_id' => $keyId,
                'secret_hash' => $secretHash,
                'active' => true,
                'ip_allowlist' => json_encode(['127.0.0.1', 'localhost']), // JSON encode for proper storage
            ]);

            $this->line("  ✓ Created API Client: {$keyId}");
            $this->line("    Secret (save this - not stored): {$secret}");
        }
    }

    /**
     * Create test clients
     */
    private function createTestClients(): void
    {
        $count = $this->option('clients');
        $this->info("Creating {$count} test clients...");

        // Get the highest existing test client number to avoid conflicts
        $existingCount = Client::where('external_id', 'LIKE', 'TEST_CLIENT_%')->count();
        $startNumber = $existingCount + 1;

        for ($i = 0; $i < $count; $i++) {
            $clientNumber = $startNumber + $i;
            
            Client::create([
                'external_id' => "TEST_CLIENT_{$clientNumber}",
                'first_name' => "Test",
                'last_name' => "Client {$clientNumber}",
                'company' => "Test Company {$clientNumber}",
                'email' => "test{$clientNumber}@example.com",
                'phone' => "+420123456" . str_pad($clientNumber, 3, '0', STR_PAD_LEFT),
                'vat_id' => "CZ" . str_pad($clientNumber, 8, '0', STR_PAD_LEFT),
                'is_active' => true,
                'meta' => [
                    'test_data' => true,
                    'created_by_seeder' => true,
                ],
            ]);

            $this->line("  ✓ Created Client: Test Company {$clientNumber}");
        }
    }

    /**
     * Create test orders
     */
    private function createTestOrders(): void
    {
        $count = $this->option('orders');
        $clients = Client::where('external_id', 'LIKE', 'TEST_CLIENT_%')->get();
        
        if ($clients->isEmpty()) {
            $this->error('No test clients found. Please create test clients first.');
            return;
        }

        $this->info("Creating {$count} test orders...");

        $statuses = [
            Order::STATUS_NEW,
            Order::STATUS_CONFIRMED,
            Order::STATUS_PAID,
            Order::STATUS_FULFILLED,
            Order::STATUS_COMPLETED,
        ];

        $carriers = [Order::CARRIER_DPD, Order::CARRIER_BALIKOVNA];

        // Get the highest existing test order number to avoid conflicts
        $existingCount = Order::where('number', 'LIKE', 'TEST-%')->count();
        $startNumber = $existingCount + 1;

        for ($i = 0; $i < $count; $i++) {
            $orderNumber = $startNumber + $i;
            $client = $clients->random();
            
            // Create shipping address
            $shippingAddress = Address::create([
                'name' => "Test Shipping Address {$orderNumber}",
                'company' => "Test Delivery Company {$orderNumber}",
                'street1' => "Test Street {$orderNumber}",
                'street2' => null,
                'city' => 'Prague',
                'state' => null,
                'postal_code' => '10000',
                'country_code' => 'CZ',
                'phone' => "+420987654{$orderNumber}",
                'email' => "delivery{$orderNumber}@example.com",
                'type' => 'shipping',
            ]);

            // Create billing address (same as shipping for simplicity)
            $billingAddress = Address::create([
                'name' => $client->first_name . ' ' . $client->last_name,
                'company' => $client->company,
                'street1' => "Test Billing Street {$orderNumber}",
                'street2' => null,
                'city' => 'Prague',
                'state' => null,
                'postal_code' => '10000',
                'country_code' => 'CZ',
                'phone' => $client->phone,
                'email' => $client->email,
                'type' => 'billing',
            ]);
            
            $carrier = $carriers[array_rand($carriers)];
            $status = $statuses[array_rand($statuses)];
            $item = OrderItemFactory::new()->count(rand(1, 5));
            
            Order::create([
                'client_id' => $client->id,
                'number' => 'TEST-' . str_pad($orderNumber, 6, '0', STR_PAD_LEFT),
                'status' => $status,
                'total_amount' => rand(100, 5000),
                'currency' => 'CZK',
                'shipping_address_id' => $shippingAddress->id,
                'billing_address_id' => $billingAddress->id,
                'carrier' => $carrier,
                'shipping_method' => $carrier === Order::CARRIER_DPD 
                    ? [Order::SHIPPING_METHOD_DPD_HOME, Order::SHIPPING_METHOD_DPD_PICKUP][array_rand([Order::SHIPPING_METHOD_DPD_HOME, Order::SHIPPING_METHOD_DPD_PICKUP])]
                    : Order::SHIPPING_METHOD_BALIKOVNA_PICKUP,
                'meta' => [
                    'notes' => "[QUEUE_TEST] Test order for queue testing suite - Order {$orderNumber}",
                    'test_data' => true,
                ],
                'items' => $item->make(),
                'created_at' => now()->subDays(rand(0, 30)),
                'updated_at' => now(),
            ]);

            $this->line("  ✓ Created Order: TEST-" . str_pad($orderNumber, 6, '0', STR_PAD_LEFT));
        }
    }

    /**
     * Dispatch test jobs to the queue
     */
    private function dispatchTestJobs(): void
    {
        $count = $this->option('jobs');
        $orders = Order::where('number', 'LIKE', 'TEST-%')->with(['client', 'shippingAddress'])->get();

        if ($orders->isEmpty()) {
            $this->warn('No test orders found. Cannot dispatch queue jobs.');
            return;
        }

        $this->info("Dispatching {$count} test jobs to the queue...");

        for ($i = 1; $i <= $count; $i++) {
            $order = $orders->random();
            $jobType = rand(1, 3);

            try {
                switch ($jobType) {
                    case 1:
                        // PDF Generation Job - with realistic test data
                        GenerateOrderPdfJob::dispatch(
                            $order,
                            [], // images array - empty for testing
                            10, // cell size
                            Storage::disk("local")->path("overlay_image/magnetky_sablona.png") // test overlay URL
                        );
                        $this->line("  ✓ Dispatched PDF Generation job for Order {$order->number}");
                        break;

                    case 2:
                        // Shipping Label Job - only for orders with carrier
                        if ($order->carrier) {
                            GenerateShippingLabel::dispatch($order, [
                                'service_type' => 'standard',
                                'test_mode' => true,
                            ]);
                            $this->line("  ✓ Dispatched Shipping Label job for Order {$order->number}");
                        } else {
                            $this->line("  ⚠ Skipped Shipping Label job for Order {$order->number} (no carrier)");
                        }
                        break;

                    case 3:
                        // Order State Change Job - with correct parameters
                        $newStatus = $order->status === Order::STATUS_NEW ? Order::STATUS_CONFIRMED : Order::STATUS_PAID;
                        ProcessOrderStateChange::dispatch(
                            $order,
                            $order->status, // previous status
                            $newStatus, // new status
                            'Test state change for queue testing', // reason
                            ['automated' => true, 'test_mode' => true] // metadata
                        );
                        $this->line("  ✓ Dispatched State Change job for Order {$order->number}");
                        break;
                }
            } catch (\Exception $e) {
                $this->error("  ✗ Failed to dispatch job for Order {$order->number}: " . $e->getMessage());
            }

            // Add some delay to simulate real-world timing
            usleep(100000); // 0.1 second
        }
    }

    /**
     * Show summary of created test data
     */
    private function showSummary(): void
    {
        $this->newLine();
        $this->info('=== Test Data Summary ===');
        
        $apiClientsCount = ApiClient::where('name', 'LIKE', '%Test API Client%')->count();
        $clientsCount = Client::where('external_id', 'LIKE', 'TEST_CLIENT_%')->count();
        $ordersCount = Order::where('number', 'LIKE', 'TEST-%')->count();
        $pendingJobsCount = DB::table('jobs')->count();
        $failedJobsCount = DB::table('failed_jobs')->count();

        $this->table(['Resource', 'Count'], [
            ['API Clients', $apiClientsCount],
            ['Test Clients', $clientsCount],
            ['Test Orders', $ordersCount],
            ['Pending Jobs', $pendingJobsCount],
            ['Failed Jobs', $failedJobsCount],
        ]);

        if ($apiClientsCount > 0) {
            $this->newLine();
            $this->info('=== API Client Credentials ===');
            $apiClients = ApiClient::where('name', 'LIKE', '%Test API Client%')
                ->select('name', 'key_id')
                ->get();
                
            foreach ($apiClients as $client) {
                $this->line("Name: {$client->name}");
                $this->line("Key ID: {$client->key_id}");
                $this->line("---");
            }
        }

        $this->newLine();
        $this->info('You can now use the Queue Testing Dashboard at /testing/queue-dashboard');
        $this->info('API Testing Interface available at /testing/api-testing');
    }
}
