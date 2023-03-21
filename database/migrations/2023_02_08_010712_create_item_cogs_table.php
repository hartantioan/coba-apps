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
        if(!Schema::hasTable('item_cogs'))
        Schema::create('item_cogs', function (Blueprint $table) {
            $table->id();
            $table->string('lookable_type',155)->nullable();
            $table->bigInteger('lookable_id')->nullable();
            $table->bigInteger('company_id')->nullable();
            $table->bigInteger('place_id')->nullable();
            $table->bigInteger('warehouse_id')->nullable();
            $table->bigInteger('item_id')->nullable();
            $table->double('qty_in')->nullable();
            $table->double('price_in')->nullable();
            $table->double('total_in')->nullable();
            $table->double('qty_out')->nullable();
            $table->double('price_out')->nullable();
            $table->double('total_out')->nullable();
            $table->double('qty_final')->nullable();
            $table->double('price_final')->nullable();
            $table->double('total_final')->nullable();
            $table->date('date')->nullable();
            $table->char('type', 3)->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['lookable_id', 'company_id', 'place_id', 'warehouse_id', 'item_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('item_cogs');
    }
};
