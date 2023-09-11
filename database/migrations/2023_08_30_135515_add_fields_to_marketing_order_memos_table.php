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
            $table->bigInteger('company_id')->nullable()->after('user_id');
            $table->bigInteger('account_id')->nullable()->after('company_id');
            $table->date('post_date')->nullable()->after('account_id');
            $table->string('tax_no',155)->nullable()->after('document');
            $table->double('total')->nullable()->after('tax_no');
            $table->double('total_after_tax')->nullable()->after('tax');
            $table->double('rounding')->nullable()->after('total_after_tax');
            $table->double('downpayment')->after('grandtotal')->nullable();
            $table->double('balance')->after('downpayment')->nullable();
            $table->bigInteger('void_id')->nullable()->after('balance');
            $table->string('void_note')->nullable()->after('void_id');
            $table->timestamp('void_date')->nullable()->after('void_note');

            $table->index(['user_id','company_id','account_id'],'mo_memos_index');
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
