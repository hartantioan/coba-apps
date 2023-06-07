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
        if(!Schema::hasTable('inventory_transfer_out_details'))
        Schema::create('inventory_transfer_out_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('inventory_transfer_out_id')->nullable();
            $table->bigInteger('item_stock_id')->nullable();
            $table->bigInteger('item_id')->nullable();
            $table->double('qty')->nullable();
            $table->double('price')->nullable();
            $table->double('total')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['inventory_transfer_out_id','item_id','item_stock_id'],'inventory_transfer_detail_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventory_transfer_out_details');
    }
};
