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
        Schema::table('adjust_rate_details', function (Blueprint $table) {
            $table->decimal('nominal_fc',20,5)->nullable()->after('lookable_id');
            $table->decimal('nominal_rate',20,5)->nullable()->after('nominal_fc');
            $table->decimal('nominal_rp',20,5)->nullable()->after('nominal_rate');
            $table->decimal('nominal_new',20,5)->nullable()->after('nominal_rp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adjust_rate_details', function (Blueprint $table) {
            $table->dropColumn('nominal_fc','nominal_rate','nominal_rp','nominal_new');
        });
    }
};
