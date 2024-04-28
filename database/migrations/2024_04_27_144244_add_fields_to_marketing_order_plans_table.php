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
        Schema::table('marketing_order_plans', function (Blueprint $table) {
            $table->bigInteger('line_id')->after('place_id')->nullable()->index();
            $table->date('start_date')->after('type')->nullable();
            $table->date('end_date')->after('start_date')->nullable();
            $table->bigInteger('marketing_order_id')->nullable()->after('end_date')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketing_order_plans', function (Blueprint $table) {
            $table->dropColumn('line_id','start_date','end_date','marketing_order_id');
        });
    }
};
