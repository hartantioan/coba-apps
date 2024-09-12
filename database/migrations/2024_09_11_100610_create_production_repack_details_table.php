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
        Schema::create('production_repack_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('production_repack_id')->nullable();
            $table->bigInteger('item_source_id')->nullable();
            $table->bigInteger('item_stock_id')->nullable();
            $table->decimal('qty',20,5)->nullable();
            $table->bigInteger('item_unit_source_id')->nullable();
            $table->bigInteger('item_target_id')->nullable();
            $table->bigInteger('item_unit_target_id')->nullable();
            $table->bigInteger('place_id')->nullable();
            $table->bigInteger('warehouse_id')->nullable();
            $table->bigInteger('item_shading_id')->nullable();
            $table->bigInteger('production_batch_id')->nullable();
            $table->bigInteger('area_id')->nullable();
            $table->string('batch_no')->nullable();
            $table->decimal('total',20,5)->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['production_repack_id','item_source_id','item_stock_id','item_unit_source_id','item_target_id','item_unit_target_id','place_id','warehouse_id','item_shading_id','production_batch_id','area_id'],'prd_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_repack_details');
    }
};
