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
        Schema::table('marketing_order_invoices', function (Blueprint $table) {
            $table->dropColumn('customer_id','branch_id','posting_date','subtotal','percent_discount','nominal_discount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketing_order_invoices', function (Blueprint $table) {
            //
        });
    }
};
