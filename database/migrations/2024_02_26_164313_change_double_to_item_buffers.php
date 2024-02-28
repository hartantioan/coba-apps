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
        Schema::table('item_buffers', function (Blueprint $table) {
            $table->decimal('min_stock', 20, 5)->change();
            $table->decimal('max_stock', 20, 5)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('item_buffers', function (Blueprint $table) {
            //
        });
    }
};
