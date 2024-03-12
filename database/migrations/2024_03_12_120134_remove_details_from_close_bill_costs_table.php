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
        Schema::table('close_bill_costs', function (Blueprint $table) {
            $table->dropColumn('tax_id','percent_tax','is_include_tax','wtax_id','percent_wtax');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('close_bill_costs', function (Blueprint $table) {
            //
        });
    }
};
