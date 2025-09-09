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
        Schema::create('orders', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('number')->unique();
            $table->string('pmi_id')->nullable()->unique()->index();
            $table->foreignUlid('client_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['new', 'confirmed', 'paid', 'fulfilled', 'completed', 'cancelled', 'on_hold', 'failed'])->index();
            $table->decimal('total_amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->foreignUlid('shipping_address_id')->nullable()->constrained('addresses')->onDelete('set null');
            $table->foreignUlid('billing_address_id')->nullable()->constrained('addresses')->onDelete('set null');
            $table->enum('carrier', ['balikovna', 'dpd'])->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('client_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
