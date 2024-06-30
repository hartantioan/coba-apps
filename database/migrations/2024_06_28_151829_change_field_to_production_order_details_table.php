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
        Schema::table('production_order_details', function (Blueprint $table) {
            $table->dropColumn('lookable_type','lookable_id','qty','qty_real','nominal','nominal_real','total','total_real');
            $table->renameColumn('bom_detail_id','production_schedule_detail_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_order_details', function (Blueprint $table) {
            //
        });
    }
};
