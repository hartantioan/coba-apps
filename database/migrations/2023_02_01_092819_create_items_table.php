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
        if(!Schema::hasTable('items'))
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 155)->nullable();
            $table->bigInteger('item_group_id')->nullable();
            $table->string('uom_unit', 50)->nullable();
            $table->string('buy_unit', 50)->nullable();
            $table->double('buy_convert')->nullable();
            $table->string('sell_unit', 50)->nullable();
            $table->double('sell_convert')->nullable();
            $table->char('is_inventory_item', 1)->nullable();
            $table->char('is_sales_item', 1)->nullable();
            $table->char('is_purchase_item', 1)->nullable();
            $table->char('is_asset', 1)->nullable();
            $table->char('is_service', 1)->nullable();
            $table->char('status', 1)->nullable();   
            $table->timestamps();
            $table->softDeletes('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('items');
    }
};
