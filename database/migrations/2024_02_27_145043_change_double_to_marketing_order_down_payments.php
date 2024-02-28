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
        Schema::table('marketing_order_down_payments', function (Blueprint $table) {
            
            $table->decimal('percent_tax', 20, 5)->change();
            $table->decimal('currency_rate', 20, 5)->change();
            $table->decimal('subtotal', 20, 5)->change();
            $table->decimal('discount', 20, 5)->change();
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
        Schema::table('marketing_order_down_payments', function (Blueprint $table) {
            //
        });
    }
};
