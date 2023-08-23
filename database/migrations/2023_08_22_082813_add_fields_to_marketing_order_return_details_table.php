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
        Schema::table('marketing_order_return_details', function (Blueprint $table) {
            $table->bigInteger('marketing_order_delivery_detail_id')->nullable()->after('marketing_order_return_id');
            $table->bigInteger('place_id')->nullable()->after('note');
            $table->bigInteger('warehouse_id')->nullable()->after('place_id');

            $table->index(['marketing_order_return_id','marketing_order_delivery_detail_id','item_id','place_id','warehouse_id'],'moreturn_detail_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketing_order_return_details', function (Blueprint $table) {
            $table->dropColumn('marketing_order_delivery_detail_id','place_id','warehouse_id');
        });
    }
};
