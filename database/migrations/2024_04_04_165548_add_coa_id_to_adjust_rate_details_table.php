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
            $table->bigInteger('coa_id')->nullable()->index()->after('lookable_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adjust_rate_details', function (Blueprint $table) {
            $table->dropColumn('coa_id');
        });
    }
};
