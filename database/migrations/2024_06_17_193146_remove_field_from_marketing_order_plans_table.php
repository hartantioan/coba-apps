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
            $table->dropColumn('start_date','end_date');
            $table->bigInteger('line_id')->nullable()->index()->after('place_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketing_order_plans', function (Blueprint $table) {
            $table->dropColumn('line_id');
        });
    }
};
