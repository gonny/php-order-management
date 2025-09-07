<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    /**
     * Display a listing of clients.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Client::query();

        // Apply filters if provided
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%");
            });
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate results
        $perPage = min($request->get('per_page', 15), 100);
        $clients = $query->with(['orders' => function ($q) {
            $q->latest()->limit(3);
        }])->paginate($perPage);

        return response()->json([
            'data' => $clients->items(),
            'meta' => [
                'current_page' => $clients->currentPage(),
                'last_page' => $clients->lastPage(),
                'per_page' => $clients->perPage(),
                'total' => $clients->total(),
            ],
            'message' => 'Clients retrieved successfully',
        ]);
    }

    /**
     * Store a newly created client.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'external_id' => ['sometimes', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:clients,email'],
            'phone' => ['sometimes', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'company' => ['sometimes', 'string', 'max:255'],
            'vat_id' => ['sometimes', 'string', 'max:255'],
            'meta' => ['sometimes', 'array'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $client = Client::create($request->all());

            return response()->json([
                'data' => $client,
                'message' => 'Client created successfully',
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Client creation failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified client.
     */
    public function show(string $id): JsonResponse
    {
        $client = Client::with(['orders', 'addresses'])->find($id);

        if (!$client) {
            return response()->json([
                'error' => 'Client not found',
            ], 404);
        }

        return response()->json([
            'data' => $client,
        ]);
    }

    /**
     * Update the specified client.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $client = Client::find($id);

        if (!$client) {
            return response()->json([
                'error' => 'Client not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'external_id' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'unique:clients,email,' . $id],
            'phone' => ['sometimes', 'string', 'max:255'],
            'first_name' => ['sometimes', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'company' => ['sometimes', 'string', 'max:255'],
            'vat_id' => ['sometimes', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
            'meta' => ['sometimes', 'array'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $client->update($request->all());

            return response()->json([
                'data' => $client->fresh(),
                'message' => 'Client updated successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Client update failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified client.
     */
    public function destroy(string $id): JsonResponse
    {
        $client = Client::find($id);

        if (!$client) {
            return response()->json([
                'error' => 'Client not found',
            ], 404);
        }

        // Check if client has orders
        if ($client->orders()->exists()) {
            return response()->json([
                'error' => 'Cannot delete client with existing orders',
                'message' => 'Client has orders and cannot be deleted. Consider deactivating instead.',
            ], 422);
        }

        try {
            $client->delete();

            return response()->json([
                'message' => 'Client deleted successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Client deletion failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
