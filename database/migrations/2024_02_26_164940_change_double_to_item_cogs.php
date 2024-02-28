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
        Schema::table('item_cogs', function (Blueprint $table) {
            $table->decimal('qty_in', 20, 5)->change();
            $table->decimal('price_in', 20, 5)->change();
            $table->decimal('total_in', 20, 5)->change();
            $table->decimal('qty_out', 20, 5)->change();
            $table->decimal('price_out', 20, 5)->change();
            $table->decimal('total_out', 20, 5)->change();
            $table->decimal('qty_final', 20, 5)->change();
            $table->decimal('price_final', 20, 5)->change();
            $table->decimal('total_final', 20, 5)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('item_cogs', function (Blueprint $table) {
            //
        });
    }
};
