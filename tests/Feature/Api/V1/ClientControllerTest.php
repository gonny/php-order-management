<?php

namespace Tests\Feature\Api\V1;

use App\Models\Client;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\ApiTestHelpers;

class ClientControllerTest extends TestCase
{
    use RefreshDatabase, ApiTestHelpers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpApiClient();
    }

    public function test_can_list_clients_with_pagination(): void
    {
        Client::factory()->count(20)->create();

        $response = $this->authenticatedGet('/api/v1/clients');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'external_id',
                        'email',
                        'phone',
                        'first_name',
                        'last_name',
                        'company',
                        'vat_id',
                        'is_active',
                        'meta',
                        'created_at',
                        'updated_at',
                        'orders',
                    ]
                ],
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ],
                'message',
            ]);

        $data = $response->json();
        $this->assertEquals(15, count($data['data'])); // Default per_page
        $this->assertEquals(20, $data['meta']['total']);
        $this->assertEquals('Clients retrieved successfully', $data['message']);
    }

    public function test_can_list_clients_with_search_filter(): void
    {
        Client::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'company' => 'Test Company',
        ]);

        Client::factory()->create([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith@example.com',
            'company' => 'Another Company',
        ]);

        // Test search by first name
        $response = $this->authenticatedGet('/api/v1/clients?search=John');
        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));

        // Test search by email
        $response = $this->authenticatedGet('/api/v1/clients?search=jane.smith');
        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));

        // Test search by company
        $response = $this->authenticatedGet('/api/v1/clients?search=Test Company');
        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
    }

    public function test_can_list_clients_with_active_filter(): void
    {
        Client::factory()->count(3)->create(['is_active' => true]);
        Client::factory()->count(2)->create(['is_active' => false]);

        // Test active filter
        $response = $this->authenticatedGet('/api/v1/clients?is_active=1');
        $response->assertStatus(200);
        $this->assertEquals(3, count($response->json('data')));

        // Test inactive filter
        $response = $this->authenticatedGet('/api/v1/clients?is_active=0');
        $response->assertStatus(200);
        $this->assertEquals(2, count($response->json('data')));
    }

    public function test_can_list_clients_with_sorting(): void
    {
        $client1 = Client::factory()->create(['first_name' => 'Alpha', 'created_at' => now()->subDay()]);
        $client2 = Client::factory()->create(['first_name' => 'Beta', 'created_at' => now()]);

        // Test sorting by first_name ASC
        $response = $this->authenticatedGet('/api/v1/clients?sort_by=first_name&sort_order=asc');
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals('Alpha', $data[0]['first_name']);

        // Test sorting by created_at DESC (default)
        $response = $this->authenticatedGet('/api/v1/clients?sort_by=created_at&sort_order=desc');
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals('Beta', $data[0]['first_name']);
    }

    public function test_can_create_client(): void
    {
        $clientData = [
            'external_id' => 'ext-123',
            'email' => 'test@example.com',
            'phone' => '+1234567890',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'company' => 'Test Company',
            'vat_id' => 'VAT123456789',
            'meta' => ['source' => 'api', 'notes' => 'Test client'],
        ];

        $response = $this->authenticatedPost('/api/v1/clients', $clientData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'external_id',
                    'email',
                    'phone',
                    'first_name',
                    'last_name',
                    'company',
                    'vat_id',
                    'meta',
                    'created_at',
                    'updated_at',
                ],
                'message',
            ])
            ->assertJson([
                'data' => $clientData,
                'message' => 'Client created successfully',
            ]);

        $this->assertDatabaseHas('clients', [
            'email' => 'test@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);
    }

    public function test_create_client_validation_fails_with_invalid_data(): void
    {
        $invalidData = [
            'email' => 'invalid-email',
            'first_name' => '', // Required field empty
            'last_name' => '', // Required field empty
        ];

        $response = $this->authenticatedPost('/api/v1/clients', $invalidData);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'error',
                'errors' => [
                    'email',
                    'first_name',
                    'last_name',
                ],
            ]);
    }

    public function test_create_client_fails_with_duplicate_email(): void
    {
        $existingClient = Client::factory()->create(['email' => 'existing@example.com']);

        $clientData = [
            'email' => 'existing@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ];

        $response = $this->authenticatedPost('/api/v1/clients', $clientData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_can_show_client(): void
    {
        $client = Client::factory()->create();
        Order::factory()->count(2)->create(['client_id' => $client->id]);

        $response = $this->authenticatedGet("/api/v1/clients/{$client->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'external_id',
                    'email',
                    'first_name',
                    'last_name',
                    'orders' => [
                        '*' => [
                            'id',
                            'number',
                            'status',
                            'total_amount',
                        ]
                    ],
                    'addresses',
                ],
            ]);

        $this->assertEquals($client->id, $response->json('data.id'));
        $this->assertEquals(2, count($response->json('data.orders')));
    }

    public function test_show_client_returns_404_for_non_existent_client(): void
    {
        $response = $this->authenticatedGet('/api/v1/clients/non-existent-id');

        $response->assertStatus(404)
            ->assertJson([
                'error' => 'Client not found',
            ]);
    }

    public function test_can_update_client(): void
    {
        $client = Client::factory()->create([
            'first_name' => 'Old Name',
            'email' => 'old@example.com',
        ]);

        $updateData = [
            'first_name' => 'New Name',
            'email' => 'new@example.com',
            'is_active' => false,
        ];

        $response = $this->authenticatedPatch("/api/v1/clients/{$client->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'first_name',
                    'email',
                    'is_active',
                ],
                'message',
            ])
            ->assertJson([
                'data' => [
                    'first_name' => 'New Name',
                    'email' => 'new@example.com',
                    'is_active' => false,
                ],
                'message' => 'Client updated successfully',
            ]);

        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'first_name' => 'New Name',
            'email' => 'new@example.com',
            'is_active' => false,
        ]);
    }

    public function test_update_client_returns_404_for_non_existent_client(): void
    {
        $response = $this->authenticatedPatch('/api/v1/clients/non-existent-id', [
            'first_name' => 'Updated Name',
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'error' => 'Client not found',
            ]);
    }

    public function test_update_client_validation_fails_with_duplicate_email(): void
    {
        $client1 = Client::factory()->create(['email' => 'client1@example.com']);
        $client2 = Client::factory()->create(['email' => 'client2@example.com']);

        $response = $this->authenticatedPatch("/api/v1/clients/{$client2->id}", [
            'email' => 'client1@example.com', // Try to use existing email
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_can_delete_client_without_orders(): void
    {
        $client = Client::factory()->create();

        $response = $this->authenticatedDelete("/api/v1/clients/{$client->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Client deleted successfully',
            ]);

        $this->assertDatabaseMissing('clients', ['id' => $client->id]);
    }

    public function test_cannot_delete_client_with_orders(): void
    {
        $client = Client::factory()->create();
        Order::factory()->create(['client_id' => $client->id]);

        $response = $this->authenticatedDelete("/api/v1/clients/{$client->id}");

        $response->assertStatus(422)
            ->assertJson([
                'error' => 'Cannot delete client with existing orders',
                'message' => 'Client has orders and cannot be deleted. Consider deactivating instead.',
            ]);

        $this->assertDatabaseHas('clients', ['id' => $client->id]);
    }

    public function test_delete_client_returns_404_for_non_existent_client(): void
    {
        $response = $this->authenticatedDelete('/api/v1/clients/non-existent-id');

        $response->assertStatus(404)
            ->assertJson([
                'error' => 'Client not found',
            ]);
    }

    public function test_client_endpoints_require_authentication(): void
    {
        // Test index
        $response = $this->getJson('/api/v1/clients');
        $response->assertStatus(401);

        // Test store
        $response = $this->postJson('/api/v1/clients', []);
        $response->assertStatus(401);

        // Test show
        $response = $this->getJson('/api/v1/clients/1');
        $response->assertStatus(401);

        // Test update
        $response = $this->patchJson('/api/v1/clients/1', []);
        $response->assertStatus(401);

        // Test delete
        $response = $this->deleteJson('/api/v1/clients/1');
        $response->assertStatus(401);
    }

    public function test_clients_with_recent_orders_included(): void
    {
        $client = Client::factory()->create();
        
        // Create more than 3 orders to test the limit
        Order::factory()->count(5)->create([
            'client_id' => $client->id,
            'created_at' => now()->subDays(rand(1, 10)),
        ]);

        $response = $this->authenticatedGet('/api/v1/clients');

        $response->assertStatus(200);
        $data = $response->json('data');
        
        $clientData = collect($data)->firstWhere('id', $client->id);
        $this->assertNotNull($clientData);
        $this->assertLessThanOrEqual(3, count($clientData['orders'])); // Should limit to 3 recent orders
    }
}