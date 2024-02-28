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
        Schema::table('purchase_order_details', function (Blueprint $table) {
            $table->decimal('qty', 20, 5)->change();
            $table->decimal('price', 20, 5)->change();
            $table->decimal('qty_conversion', 20, 5)->change();
            $table->decimal('percent_tax', 20, 5)->change();
            $table->decimal('percent_discount_1', 20, 5)->change();
            $table->decimal('percent_discount_2', 20, 5)->change();
            $table->decimal('discount_3', 20, 5)->change();
            $table->decimal('subtotal', 20, 5)->change();
            $table->decimal('percent_wtax', 20, 5)->change();
          
            $table->decimal('tax', 20, 5)->change();
            $table->decimal('wtax', 20, 5)->change();
            $table->decimal('grandtotal', 20, 5)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_order_details', function (Blueprint $table) {
            //
        });
    }
};
