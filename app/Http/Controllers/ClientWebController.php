<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClientWebController extends Controller
{
    /**
     * Display a listing of clients.
     */
    public function index(Request $request): Response
    {
        $clients = Client::withCount('orders')
            ->latest()
            ->paginate(15);

        return Inertia::render('clients/Index', [
            'clients' => $clients,
            'filters' => $request->only(['search', 'is_active']),
        ]);
    }

    /**
     * Show the form for creating a new client.
     */
    public function create(): Response
    {
        return Inertia::render('clients/Create');
    }

    /**
     * Display the specified client.
     */
    public function show(Client $client): Response
    {
        $client->load(['orders' => function ($query) {
            $query->latest()->with(['items'])->limit(10);
        }, 'addresses']);

        return Inertia::render('clients/Show', [
            'client' => $client,
        ]);
    }

    /**
     * Show the form for editing the specified client.
     */
    public function edit(Client $client): Response
    {
        $client->load(['addresses']);

        return Inertia::render('clients/Edit', [
            'client' => $client,
        ]);
    }
}
