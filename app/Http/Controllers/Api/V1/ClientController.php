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
}
