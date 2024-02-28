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
        Schema::table('purchase_invoices', function (Blueprint $table) {
            $table->decimal('rounding', 20, 5)->change();
            $table->decimal('downpayment', 20, 5)->change();
            $table->decimal('balance', 20, 5)->change();
           
            $table->decimal('total', 20, 5)->change();
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
        Schema::table('purchase_invoices', function (Blueprint $table) {
            //
        });
    }
};
