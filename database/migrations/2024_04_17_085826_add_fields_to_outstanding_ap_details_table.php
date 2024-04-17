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
        Schema::table('outstanding_ap_details', function (Blueprint $table) {
            $table->decimal('currency_rate',20,5)->nullable()->after('balance');
            $table->decimal('balance_fc',20,5)->nullable()->after('currency_rate');
            $table->string('note')->nullable()->after('balance_fc');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('outstanding_ap_details', function (Blueprint $table) {
            $table->dropColumn('currency_rate','balance_fc','note');
        });
    }
};
