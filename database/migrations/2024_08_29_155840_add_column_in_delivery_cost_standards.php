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
        Schema::table('delivery_cost_standards', function (Blueprint $table) {
            $table->dropColumn('category_transportation');
            $table->bigInteger('transportation_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_cost_standards', function (Blueprint $table) {
            //
        });
    }
};
