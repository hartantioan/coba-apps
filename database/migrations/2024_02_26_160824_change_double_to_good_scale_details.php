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
        Schema::table('good_scale_details', function (Blueprint $table) {
            $table->decimal('qty_in', 20, 5)->change();
            $table->decimal('qty_out', 20, 5)->change();
            $table->decimal('qty_balance', 20, 5)->change();
            $table->decimal('qty_conversion', 20, 5)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('good_scale_details', function (Blueprint $table) {
            //
        });
    }
};
