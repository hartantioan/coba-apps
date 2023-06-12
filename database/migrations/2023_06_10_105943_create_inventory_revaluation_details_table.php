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
        Schema::create('inventory_revaluation_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('inventory_revaluation_id')->nullable();
            $table->bigInteger('item_stock_id')->nullable();
            $table->bigInteger('item_id')->nullable();
            $table->double('qty')->nullable();
            $table->bigInteger('place_id')->nullable();
            $table->bigInteger('warehouse_id')->nullable();
            $table->double('nominal')->nullable();
            $table->bigInteger('coa_id')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['inventory_revaluation_id','item_stock_id','item_id','place_id','warehouse_id'],'inventory_revaluation_details_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventory_revaluation_details');
    }
};
