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
        Schema::create('good_scale_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('good_scale_id')->nullable();
            $table->bigInteger('purchase_order_detail_id')->nullable();
            $table->bigInteger('item_id')->nullable();
            $table->double('qty_in')->nullable();
            $table->double('qty_out')->nullable();
            $table->double('qty_balance')->nullable();
            $table->string('note')->nullable();
            $table->string('note2')->nullable();
            $table->bigInteger('place_id')->nullable();
            $table->bigInteger('warehouse_id')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');
            
            $table->index(['good_scale_id','purchase_order_detail_id','item_id','place_id','warehouse_id'],'good_scale_details_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('good_scale_details');
    }
};
