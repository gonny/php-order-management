<?php

namespace App\Console\Commands;

use App\Jobs\GenerateOrderPdfJob;
use App\Jobs\GenerateShippingLabel;
use App\Jobs\ProcessOrderStateChange;
use App\Models\ApiClient;
use App\Models\Client;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Queue;
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
        if ($this->option('clear')) {
            $this->clearTestData();
        }

        $this->info('Seeding test data for queue testing suite...');

        // Create API clients for testing
        $this->createApiClients();

        // Create test clients
        $this->createTestClients();

        // Create test orders
        $this->createTestOrders();

        // Dispatch test jobs
        $this->dispatchTestJobs();

        $this->info('Test data seeding completed successfully!');
        $this->showSummary();
    }

    /**
     * Clear existing test data
     */
    private function clearTestData(): void
    {
        $this->warn('Clearing existing test data...');
        
        // Clear queue jobs
        \DB::table('jobs')->delete();
        \DB::table('failed_jobs')->delete();
        
        // Clear test orders and clients (only those created for testing)
        Order::where('number', 'LIKE', 'TEST-%')->delete();
        Client::where('external_id', 'LIKE', 'TEST_CLIENT_%')->delete();
        ApiClient::where('name', 'LIKE', '%Test API Client%')->delete();
        
        $this->info('Test data cleared.');
    }

    /**
     * Create API clients for testing
     */
    private function createApiClients(): void
    {
        $count = $this->option('api-clients');
        $this->info("Creating {$count} API clients for testing...");

        for ($i = 1; $i <= $count; $i++) {
            $keyId = 'test_' . Str::random(16);
            $secretHash = hash('sha256', Str::random(64));

            ApiClient::create([
                'name' => "Test API Client {$i}",
                'key_id' => $keyId,
                'secret_hash' => $secretHash,
                'active' => true,
                'ip_allowlist' => ['127.0.0.1', 'localhost'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->line("  ✓ Created API Client: {$keyId}");
        }
    }

    /**
     * Create test clients
     */
    private function createTestClients(): void
    {
        $count = $this->option('clients');
        $this->info("Creating {$count} test clients...");

        for ($i = 1; $i <= $count; $i++) {
            Client::create([
                'external_id' => "TEST_CLIENT_{$i}",
                'first_name' => "Test",
                'last_name' => "Client {$i}",
                'company' => "Test Company {$i}",
                'email' => "test{$i}@example.com",
                'phone' => "+420123456{$i}",
                'vat_id' => "CZ" . str_pad($i, 8, '0', STR_PAD_LEFT),
                'is_active' => true,
            ]);

            $this->line("  ✓ Created Client: Test Company {$i}");
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
            $this->warn('No test clients found. Creating orders without client association.');
        }

        $this->info("Creating {$count} test orders...");

        $statuses = ['new', 'confirmed', 'paid', 'fulfilled', 'completed'];

        for ($i = 1; $i <= $count; $i++) {
            $client = $clients->random() ?? null;
            
            Order::create([
                'client_id' => $client?->id,
                'number' => 'TEST-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'status' => $statuses[array_rand($statuses)],
                'total_amount' => rand(100, 5000),
                'currency' => 'CZK',
                'meta' => [
                    'notes' => "[QUEUE_TEST] Test order for queue testing suite - Order {$i}",
                    'delivery_company_name' => "Test Delivery Company {$i}",
                    'delivery_contact_person' => "Delivery Contact {$i}",
                    'delivery_street' => "Test Street {$i}",
                    'delivery_city' => 'Prague',
                    'delivery_postal_code' => '10000',
                    'delivery_country' => 'CZ',
                    'delivery_phone' => "+420987654{$i}",
                    'delivery_email' => "delivery{$i}@example.com",
                ],
                'created_at' => now()->subDays(rand(0, 30)),
                'updated_at' => now(),
            ]);

            $this->line("  ✓ Created Order: TEST-" . str_pad($i, 6, '0', STR_PAD_LEFT));
        }
    }

    /**
     * Dispatch test jobs to the queue
     */
    private function dispatchTestJobs(): void
    {
        $count = $this->option('jobs');
        $orders = Order::where('number', 'LIKE', 'TEST-%')->get();

        if ($orders->isEmpty()) {
            $this->warn('No test orders found. Cannot dispatch queue jobs.');
            return;
        }

        $this->info("Dispatching {$count} test jobs to the queue...");

        for ($i = 1; $i <= $count; $i++) {
            $order = $orders->random();
            $jobType = rand(1, 3);

            switch ($jobType) {
                case 1:
                    // PDF Generation Job
                    GenerateOrderPdfJob::dispatch(
                        $order,
                        [], // images array
                        10, // cell size
                        'https://example.com/overlay.png' // overlay URL
                    )->onQueue('default');
                    $this->line("  ✓ Dispatched PDF Generation job for Order {$order->number}");
                    break;

                case 2:
                    // Shipping Label Job
                    GenerateShippingLabel::dispatch($order, [
                        'carrier' => 'dpd',
                        'service_type' => 'standard',
                    ])->onQueue('default');
                    $this->line("  ✓ Dispatched Shipping Label job for Order {$order->number}");
                    break;

                case 3:
                    // Order State Change Job
                    ProcessOrderStateChange::dispatch($order, 'confirmed', [
                        'reason' => 'Test state change for queue testing',
                        'automated' => true,
                    ])->onQueue('default');
                    $this->line("  ✓ Dispatched State Change job for Order {$order->number}");
                    break;
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
        $pendingJobsCount = \DB::table('jobs')->count();
        $failedJobsCount = \DB::table('failed_jobs')->count();

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
