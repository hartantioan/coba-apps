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
        Schema::table('marketing_order_memo_details', function (Blueprint $table) {
            $table->string('lookable_type',155)->after('marketing_order_memo_id')->nullable();
            $table->bigInteger('lookable_id')->after('lookable_type')->nullable();
            $table->char('is_include_tax',1)->after('lookable_id')->nullable();
            $table->double('percent_tax')->after('is_include_tax')->nullable();
            $table->bigInteger('tax_id')->after('percent_tax')->nullable();
            $table->double('total')->after('tax_id')->nullable();
            $table->double('tax')->after('total')->nullable();
            $table->double('total_after_tax')->after('tax')->nullable();
            $table->double('rounding')->after('total_after_tax')->nullable();
            $table->double('grandtotal')->after('rounding')->nullable();
            $table->double('downpayment')->after('grandtotal')->nullable();
            $table->double('balance')->after('downpayment')->nullable();
            $table->string('note')->after('balance')->nullable();

            $table->index(['marketing_order_memo_id','lookable_id','tax_id'],'momd_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketing_order_memo_details', function (Blueprint $table) {
            //
        });
    }
};
