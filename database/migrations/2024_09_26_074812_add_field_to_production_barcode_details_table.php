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
            $table->bigInteger('bom_id')->nullable()->index()->after('item_id');
            $table->bigInteger('item_unit_id')->nullable()->index()->after('bom_id');
            $table->decimal('qty_sell',20,5)->nullable()->after('shading');
            $table->decimal('qty',20,5)->nullable()->after('qty_sell');
            $table->decimal('conversion',20,5)->nullable()->after('qty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_barcode_details', function (Blueprint $table) {
            $table->dropColumn('item_unit_id','bom_id','qty_sell','qty','conversion');
        });
    }
};
