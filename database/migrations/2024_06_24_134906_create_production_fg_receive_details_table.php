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
        Schema::create('production_fg_receive_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('production_fg_receive_id')->nullable();
            $table->string('pallet_no',155)->nullable();
            $table->bigInteger('item_id')->nullable();
            $table->decimal('qty_sell',20,5)->nullable();
            $table->decimal('qty',20,5)->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['production_fg_receive_id','item_id'],'pfrd_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_fg_receive_details');
    }
};
