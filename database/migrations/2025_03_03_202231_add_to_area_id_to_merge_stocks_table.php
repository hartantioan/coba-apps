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
        Schema::table('merge_stocks', function (Blueprint $table) {
            $table->bigInteger('to_area_id')->nullable()->index()->after('to_warehouse_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('merge_stocks', function (Blueprint $table) {
            $table->dropColumn('to_area_id');
        });
    }
};
