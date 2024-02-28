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
        Schema::table('items', function (Blueprint $table) {
            $table->decimal('buy_convert', 20, 5)->change();
            $table->decimal('sell_convert', 20, 5)->change();
            $table->decimal('pallet_convert', 20, 5)->change();
            $table->decimal('production_convert', 20, 5)->change();
            $table->decimal('tolerance_gr', 20, 5)->change();
            $table->decimal('min_stock', 20, 5)->change();
            $table->decimal('max_stock', 20, 5)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            //
        });
    }
};
