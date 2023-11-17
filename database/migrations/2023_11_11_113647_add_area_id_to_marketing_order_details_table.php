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
            $table->bigInteger('area_id')->nullable()->after('warehouse_id');
            $table->index(['area_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketing_order_details', function (Blueprint $table) {
            $table->dropColumn('area_id');
        });
    }
};
