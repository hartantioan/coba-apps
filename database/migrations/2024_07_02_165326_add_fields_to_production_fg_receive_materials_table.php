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
        Schema::table('production_fg_receive_materials', function (Blueprint $table) {
            $table->decimal('total',20,5)->nullable()->after('item_stock_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_fg_receive_materials', function (Blueprint $table) {
            $table->dropColumn('total');
        });
    }
};
