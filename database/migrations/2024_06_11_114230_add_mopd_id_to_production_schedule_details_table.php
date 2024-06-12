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
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_schedule_details', function (Blueprint $table) {
            $table->dropColumn('marketing_order_plan_detail_id');
        });
    }
};
