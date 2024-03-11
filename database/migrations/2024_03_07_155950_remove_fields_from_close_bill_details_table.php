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
        Schema::table('close_bill_details', function (Blueprint $table) {
            $table->dropColumn('coa_id','cost_distribution_id','tax_id','is_include_tax','percent_tax','wtax_id','percent_wtax','total','tax','wtax','grandtotal','balance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('close_bill_details', function (Blueprint $table) {
            //
        });
    }
};
