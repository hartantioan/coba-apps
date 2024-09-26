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
        Schema::table('production_barcode_details', function (Blueprint $table) {
            $table->dropColumn('item_unit_id','bom_id','qty_sell','qty','conversion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_barcode_details', function (Blueprint $table) {
            //
        });
    }
};
