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
        Schema::table('marketing_order_details', function (Blueprint $table) {
            $table->decimal('qty', 20, 5)->change();
            $table->decimal('price', 20, 5)->change();
            $table->decimal('margin', 20, 5)->change();
            $table->decimal('percent_tax', 20, 5)->change();
            $table->decimal('percent_discount_1', 20, 5)->change();
            $table->decimal('percent_discount_2', 20, 5)->change();
            $table->decimal('discount_3', 20, 5)->change();
            $table->decimal('other_fee', 20, 5)->change();
            $table->decimal('price_after_discount', 20, 5)->change();
            $table->decimal('total', 20, 5)->change();
            $table->decimal('tax', 20, 5)->change();
            $table->decimal('grandtotal', 20, 5)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketing_order_details', function (Blueprint $table) {
            //
        });
    }
};
