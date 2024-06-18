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
        Schema::create('production_receive_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('production_receive_id')->nullable();
            $table->biginteger('production_order_id')->nullable();
            $table->bigInteger('item_id')->nullable();
            $table->bigInteger('bom_id')->nullable();
            $table->decimal('qty',20,5)->nullable();
            $table->bigInteger('place_id')->nullable();
            $table->bigInteger('warehouse_id')->nullable(); 
            $table->bigInteger('tank_id')->nullable();
            $table->string('batch_no',155)->nullable();
            $table->bigInteger('production_batch_id')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');
            
            $table->index(['production_receive_id','production_order_id','item_id','bom_id','place_id','warehouse_id','tank_id','production_batch_id'],'prd_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_receive_details');
    }
};
