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
            $table->char('invoice_type',1)->nullable()->after('type');  
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketing_order_invoices', function (Blueprint $table) {
            $table->dropColumn('invoice_type');
        });
    }
};
