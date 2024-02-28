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
        Schema::table('document_tax_details', function (Blueprint $table) {
            $table->decimal('price', 20, 5)->change();
            $table->decimal('qty', 20, 5)->change();
            $table->decimal('subtotal', 20, 5)->change();
            $table->decimal('discount', 20, 5)->change();
            $table->decimal('tax', 20, 5)->change();
            $table->decimal('nominal_ppnbm', 20, 5)->change();
            $table->decimal('total', 20, 5)->change();
            $table->decimal('ppnbm', 20, 5)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_tax_details', function (Blueprint $table) {
            //
        });
    }
};
