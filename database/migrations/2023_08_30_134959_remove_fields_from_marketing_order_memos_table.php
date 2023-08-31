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
        Schema::table('marketing_order_memos', function (Blueprint $table) {
            $table->dropColumn('marketing_order_invoice_id','customer_id','branch_id','posting_date','due_date','document_date','subtotal','percent_discount','nominal_discount','down_payment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketing_order_memos', function (Blueprint $table) {
            //
        });
    }
};
