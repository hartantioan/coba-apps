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
        Schema::table('marketing_orders', function (Blueprint $table) {
            $table->dropColumn('shipment_address');
            $table->bigInteger('transportation_id')->nullable()->after('sender_id');
            $table->bigInteger('outlet_id')->nullable()->after('delivery_date');

            $table->index(['transportation_id','outlet_id'],'transport_outlet_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketing_orders', function (Blueprint $table) {
            //
        });
    }
};
