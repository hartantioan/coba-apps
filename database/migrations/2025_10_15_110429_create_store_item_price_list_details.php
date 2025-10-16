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
        Schema::create('store_item_price_list_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('store_item_price_list_id')->nullable();
            $table->bigInteger('selling_category_id')->nullable();
            $table->decimal('price',20,5)->nullable();
            $table->decimal('discount',20,5)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_item_price_list_details');
    }
};
