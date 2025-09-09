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
        Schema::create('order_items', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('order_id')->constrained()->onDelete('cascade');
            $table->string('sku');
            $table->string('name');
            $table->integer('qty');
            $table->decimal('price', 10, 2);
            $table->decimal('tax_rate', 5, 4)->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index('order_id');
            $table->index('sku');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
