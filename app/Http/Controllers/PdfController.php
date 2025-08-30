<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PdfController extends Controller
{
    /**
     * Download PDF for an order.
     */
    public function downloadOrderPdf(Request $request, Order $order): BinaryFileResponse|Response
    {
        // Check if user has permission to download PDFs
        if (!$request->user() || !in_array($request->user()->role ?? '', ['admin', 'user'])) {
            abort(403, 'Unauthorized access to PDF download');
        }

        // Check if PDF exists for this order
        if (!$order->pdf_path) {
            abort(404, 'PDF not found for this order');
        }

        // Check if PDF file exists on disk
        if (!Storage::disk('pdfs')->exists($order->pdf_path)) {
            abort(404, 'PDF file not found on storage');
        }

        $filePath = Storage::disk('pdfs')->path($order->pdf_path);
        
        return response()->download(
            $filePath,
            "order_{$order->number}_grid.pdf",
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="order_' . $order->number . '_grid.pdf"',
            ]
        );
    }

    /**
     * Show PDF generation form for an order.
     */
    public function showGenerationForm(Request $request, Order $order)
    {
        // Check if user has permission
        if (!$request->user() || !in_array($request->user()->role ?? '', ['admin', 'user'])) {
            abort(403, 'Unauthorized access');
        }

        return inertia('Orders/PdfGeneration', [
            'order' => $order->load(['client', 'items']),
            'pdfExists' => !empty($order->pdf_path),
        ]);
    }
}
