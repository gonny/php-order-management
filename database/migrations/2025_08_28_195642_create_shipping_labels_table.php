<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shipping_labels', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('order_id')->constrained()->onDelete('cascade');
            $table->enum('carrier', ['balikovna', 'dpd']);
            $table->string('carrier_shipment_id')->nullable();
            $table->string('tracking_number')->nullable();
            $table->string('file_path')->nullable();
            $table->enum('format', ['pdf', 'png'])->default('pdf');
            $table->enum('status', ['generated', 'failed', 'voided'])->default('generated');
            $table->json('raw_response')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'status']);
            $table->index('tracking_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_labels');
    }
};
