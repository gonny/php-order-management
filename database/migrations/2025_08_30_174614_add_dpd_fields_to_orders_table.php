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
            $table->enum('shipping_method', ['DPD_Home', 'DPD_PickupPoint', 'Balikovna_PickupPoint'])->nullable()->after('carrier');
            $table->string('pickup_point_id', 50)->nullable()->after('shipping_method');
            $table->string('pdf_label_path')->nullable()->after('pickup_point_id');
            $table->string('dpd_shipment_id', 100)->nullable()->after('pdf_label_path');
            $table->string('parcel_group_id', 100)->nullable()->after('dpd_shipment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'shipping_method',
                'pickup_point_id',
                'pdf_label_path',
                'dpd_shipment_id',
                'parcel_group_id',
            ]);
        });
    }
};
