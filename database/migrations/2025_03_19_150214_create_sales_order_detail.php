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
        Schema::create('sales_order_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('sales_order_id')->nullable();
            $table->bigInteger('item_id')->nullable();
            $table->decimal('price',20,5)->nullable();
            $table->decimal('total',20,5)->nullable();
            $table->decimal('tax',20,5)->nullable();
            $table->decimal('wtax',20,5)->nullable();
            $table->decimal('grandtotal',20,5)->nullable();
            $table->double('percent_discount_1')->nullable();
            $table->double('percent_discount_2')->nullable();
            $table->double('discount_3')->nullable();
            $table->double('price_after_discount')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_order_details');
    }
};
