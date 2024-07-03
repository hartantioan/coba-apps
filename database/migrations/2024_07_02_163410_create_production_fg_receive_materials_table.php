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
        Schema::create('production_fg_receive_materials', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('production_fg_receive_detail_id')->nullable();
            $table->string('bom_detail_id',155)->nullable();
            $table->decimal('qty',20,5)->nullable();
            $table->bigInteger('item_stock_id')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['production_fg_receive_detail_id','bom_detail_id','item_stock_id'],'pfrm_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_fg_receive_materials');
    }
};
