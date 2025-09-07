<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Queue;
use Inertia\Inertia;
use Inertia\Response;

class QueueWebController extends Controller
{
    /**
     * Display the queue monitoring dashboard.
     */
    public function index(): Response
    {
        return Inertia::render('queues/Index');
    }

    /**
     * Display failed jobs.
     */
    public function failed(): Response
    {
        return Inertia::render('queues/Failed');
    }
}
