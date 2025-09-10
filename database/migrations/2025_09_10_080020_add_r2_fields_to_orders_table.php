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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('remote_session_id')->nullable()->after('pdf_path');
            $table->json('r2_photo_links')->nullable()->after('remote_session_id');
            $table->json('local_photo_paths')->nullable()->after('r2_photo_links');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['remote_session_id', 'r2_photo_links', 'local_photo_paths']);
        });
    }
};
