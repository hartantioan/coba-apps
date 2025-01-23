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
        Schema::table('landed_cost_fees', function (Blueprint $table) {
            $table->renameColumn('is_special','to_stock');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('landed_cost_fees', function (Blueprint $table) {
            $table->renameColumn('to_stock','is_special');
        });
    }
};
