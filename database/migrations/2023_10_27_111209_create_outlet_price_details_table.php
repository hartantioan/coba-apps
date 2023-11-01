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
        Schema::create('outlet_price_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('outlet_price_id')->nullable();
            $table->bigInteger('item_id')->nullable();
            $table->double('price')->nullable();
            $table->double('margin')->nullable();
            $table->double('percent_discount_1')->nullable();
            $table->double('percent_discount_2')->nullable();
            $table->double('discount_3')->nullable();
            $table->double('final_price')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['outlet_price_id','item_id'],'opd_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outlet_price_details');
    }
};
