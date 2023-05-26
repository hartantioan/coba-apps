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
        if(!Schema::hasTable('landed_cost_details'))
        Schema::create('landed_cost_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('landed_cost_id')->nullable();
            $table->bigInteger('item_id')->nullable();
            $table->bigInteger('coa_id')->nullable();
            $table->double('qty')->nullable();
            $table->double('nominal')->nullable();
            $table->bigInteger('place_id')->nullable();
            $table->bigInteger('department_id')->nullable();
            $table->bigInteger('warehouse_id')->nullable();
            $table->string('lookable_type',155)->nullable();
            $table->bigInteger('lookable_id')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['landed_cost_id', 'item_id', 'coa_id', 'place_id', 'department_id', 'warehouse_id'], 'landed_cost_detail_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('landed_cost_details');
    }
};
