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
        Schema::table('receive_glazes', function (Blueprint $table) {
            $table->bigInteger('item_stock_id')->nullable()->after('to_warehouse_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('receive_glazes', function (Blueprint $table) {
            $table->dropColumn('item_stock_id');
        });
    }
};
