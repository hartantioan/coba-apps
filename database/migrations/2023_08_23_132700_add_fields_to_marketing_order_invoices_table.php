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
            $table->bigInteger('account_id')->after('user_id')->nullable();
            $table->bigInteger('company_id')->after('account_id')->nullable();
            $table->date('post_date')->after('company_id')->nullable();
            $table->double('downpayment')->after('grandtotal')->nullable();
            $table->double('rounding')->after('downpayment')->nullable();
            $table->double('balance')->after('rounding')->nullable();
            $table->bigInteger('void_id')->nullable()->after('note');
            $table->string('void_note')->nullable()->after('void_id');
            $table->timestamp('void_date')->nullable()->after('void_note');

            $table->index(['account_id','company_id','user_id'],'moi_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketing_order_invoices', function (Blueprint $table) {
            $table->dropColumn('account_id','company_id','post_date','downpayment','rounding','balance','void_id','void_note','void_date');
        });
    }
};
