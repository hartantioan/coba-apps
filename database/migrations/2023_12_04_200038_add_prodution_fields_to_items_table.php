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
        Schema::table('items', function (Blueprint $table) {
            $table->bigInteger('production_unit')->nullable()->index()->after('pallet_convert');
            $table->double('production_convert')->nullable()->after('production_unit');
            
            $table->index(['item_group_id','buy_unit','sell_unit','pallet_unit'],'item_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('production_unit','production_convert');
        });
    }
};
