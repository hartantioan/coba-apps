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
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->date('received_date')->nullable()->after('delivery_date');
            $table->date('due_date')->nullable()->after('received_date');
            $table->date('document_date')->nullable()->after('due_date');
            $table->string('tax_no',155)->nullable()->after('document_date');
            $table->string('tax_cut_no',155)->nullable()->after('tax_no');
            $table->date('cut_date')->nullable()->after('tax_cut_no');
            $table->string('spk_no',155)->nullable()->after('cut_date');
            $table->string('invoice_no',155)->nullable()->after('spk_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn('received_date','due_date','document_date','tax_no','tax_cut_no','cut_date','spk_no','invoice_no');
        });
    }
};
