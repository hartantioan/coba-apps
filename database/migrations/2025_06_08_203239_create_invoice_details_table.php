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
        Schema::create('invoice_details', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('invoice_id')->nullable();
            $table->bigInteger('store_item_stock_id')->nullable();

            $table->decimal('qty', 20, 5)->nullable();
            $table->decimal('price', 20, 5)->nullable();
            $table->decimal('total', 20, 5)->nullable();

            $table->decimal('tax', 20, 5)->nullable();
            $table->decimal('wtax', 20, 5)->nullable();
            $table->decimal('discount', 20, 5)->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_details');
    }
};
