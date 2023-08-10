<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('marketing_order_delivery_details', function (Blueprint $table) {
            $table->bigInteger('marketing_order_detail_id')->nullable()->after('marketing_order_delivery_id');
            $table->bigInteger('item_stock_id')->nullable()->after('note');
            $table->bigInteger('place_id')->nullable()->after('item_stock_id');
            $table->bigInteger('warehouse_id')->nullable()->after('place_id');
            $table->index(['marketing_order_detail_id','marketing_order_delivery_id','item_id','item_stock_id','place_id','warehouse_id'],'modd_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('marketing_order_delivery_details', function (Blueprint $table) {
            $table->dropColumn('marketing_order_detail_id');
        });
    }
};
