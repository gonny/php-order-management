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
        Schema::create('webhooks', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('source');
            $table->string('event_type');
            $table->boolean('signature_valid')->default(false);
            $table->integer('http_status')->nullable();
            $table->json('headers')->nullable();
            $table->longText('payload');
            $table->timestamp('processed_at')->nullable();
            $table->text('processing_result')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamps();
            
            $table->index(['source', 'event_type']);
            $table->index(['processed_at', 'retry_count']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhooks');
    }
};
