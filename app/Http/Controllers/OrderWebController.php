<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderWebController extends Controller
{
    /**
     * Display a listing of orders.
     */
    public function index(Request $request): Response
    {
        $orders = Order::with(['client', 'items'])
            ->latest()
            ->paginate(15);

        return Inertia::render('orders/Index', [
            'orders' => $orders,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    /**
     * Show the form for creating a new order.
     */
    public function create(): Response
    {
        return Inertia::render('orders/Create');
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order): Response
    {
        $order->load(['client', 'items', 'shippingAddress', 'billingAddress', 'labels']);

        return Inertia::render('orders/Show', [
            'order' => $order,
        ]);
    }

    /**
     * Show the form for editing the specified order.
     */
    public function edit(Order $order): Response
    {
        $order->load(['client', 'items', 'shippingAddress', 'billingAddress']);

        return Inertia::render('orders/Edit', [
            'order' => $order,
        ]);
    }
}