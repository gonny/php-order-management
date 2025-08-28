<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'data' => [],
            'message' => 'Orders endpoint - authenticated successfully'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        return response()->json([
            'message' => 'Order creation endpoint - authenticated successfully'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return response()->json([
            'message' => 'Order show endpoint - authenticated successfully'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        return response()->json([
            'message' => 'Order update endpoint - authenticated successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return response()->json([
            'message' => 'Order delete endpoint - authenticated successfully'
        ]);
    }

    /**
     * Transition order to new state
     */
    public function transition(Request $request, string $id)
    {
        return response()->json([
            'message' => 'Order transition endpoint - authenticated successfully'
        ]);
    }

    /**
     * Generate shipping label
     */
    public function generateLabel(Request $request, string $id)
    {
        return response()->json([
            'message' => 'Label generation endpoint - authenticated successfully'
        ]);
    }
}
