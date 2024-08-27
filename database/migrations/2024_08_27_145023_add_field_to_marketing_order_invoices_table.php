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
            $table->decimal('subtotal',20,5)->nullable()->after('note');
            $table->decimal('downpayment',20,5)->nullable()->after('subtotal');
            $table->decimal('total',20,5)->nullable()->after('downpayment');
            $table->decimal('tax',20,5)->nullable()->after('total');
            $table->decimal('grandtotal',20,5)->nullable()->after('tax');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketing_order_invoices', function (Blueprint $table) {
            $table->dropColumn('subtotal','downpayment','total','tax','grandtotal');
        });
    }
};
