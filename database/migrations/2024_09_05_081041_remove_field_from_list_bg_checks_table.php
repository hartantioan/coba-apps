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
        Schema::table('list_bg_checks', function (Blueprint $table) {
            $table->dropColumn('bank_source_name','bank_source_no');
            $table->bigInteger('coa_id')->nullable()->after('pay_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('list_bg_checks', function (Blueprint $table) {
            $table->dropColumn('coa_id');
        });
    }
};
