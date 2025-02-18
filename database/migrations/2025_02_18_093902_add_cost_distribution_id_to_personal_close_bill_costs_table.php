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
        Schema::table('personal_close_bill_costs', function (Blueprint $table) {
            $table->bigInteger('cost_distribution_id')->nullable()->after('place_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personal_close_bill_costs', function (Blueprint $table) {
            $table->dropColumn('cost_distribution_id');
        });
    }
};
