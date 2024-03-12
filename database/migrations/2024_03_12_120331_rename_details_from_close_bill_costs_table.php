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
            $table->renameColumn('total','nominal_debit');
            $table->renameColumn('tax','nominal_credit');
            $table->renameColumn('wtax','nominal_debit_fc');
            $table->renameColumn('grandtotal','nominal_credit_fc');
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
