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
            $table->id();
            $table->string('number')->unique();
            $table->string('pmi_id')->unique()->index();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['new', 'confirmed', 'paid', 'fulfilled', 'completed', 'cancelled', 'on_hold', 'failed'])->index();
            $table->decimal('total_amount', 10, 2);
            $table->string('currency', 3)->default('EUR');
            $table->foreignId('shipping_address_id')->nullable()->constrained('addresses')->onDelete('set null');
            $table->foreignId('billing_address_id')->nullable()->constrained('addresses')->onDelete('set null');
            $table->enum('carrier', ['balikovna', 'dpd'])->nullable();
            $table->unsignedBigInteger('label_id')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'created_at']);
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
