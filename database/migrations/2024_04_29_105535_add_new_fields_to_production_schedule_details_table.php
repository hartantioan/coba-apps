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
        Schema::table('production_schedule_details', function (Blueprint $table) {
            $table->bigInteger('marketing_order_plan_detail_id')->nullable()->after('production_schedule_id')->index();
            $table->date('start_date')->nullable()->after('warehouse_id');
            $table->date('end_date')->nullable()->after('start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_schedule_details', function (Blueprint $table) {
            $table->dropColumn('marketing_order_plan_detail_id','start_date','end_date');
        });
    }
};
