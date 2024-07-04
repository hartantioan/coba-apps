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
        Schema::create('production_handover_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('production_handover_id')->nullable();
            $table->bigInteger('production_fg_receive_detail_id')->nullable();
            $table->bigInteger('item_id')->nullable();
            $table->decimal('qty',20,5)->nullable();
            $table->string('shading',50)->nullable();
            $table->bigInteger('place_id')->nullable();
            $table->bigInteger('warehouse_id')->nullable();
            $table->bigInteger('area_id')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['production_handover_id','production_fg_receive_detail_id','item_id','place_id','warehouse_id','area_id'],'phd_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_handover_details');
    }
};
