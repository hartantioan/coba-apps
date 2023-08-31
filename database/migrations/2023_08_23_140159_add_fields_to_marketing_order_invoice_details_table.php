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
        Schema::table('marketing_order_invoice_details', function (Blueprint $table) {
            $table->string('lookable_type',155)->nullable()->after('marketing_order_invoice_id');
            $table->bigInteger('lookable_id')->nullable()->after('lookable_type');
            $table->double('qty')->nullable()->after('lookable_id');
            $table->double('price')->nullable()->after('qty');
            $table->char('is_include_tax')->nullable()->after('price');
            $table->double('percent_tax')->nullable()->after('is_include_tax');
            $table->bigInteger('tax_id')->nullable()->after('percent_tax');
            $table->double('total')->nullable()->after('tax_id');
            $table->double('tax')->nullable()->after('total');
            $table->double('grandtotal')->nullable()->after('tax');
            $table->string('note')->nullable()->after('grandtotal');
            $table->timestamp('deleted_at')->nullable()->after('updated_at');

            $table->index(['marketing_order_invoice_id','lookable_id','tax_id'],'moid_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketing_order_invoice_details', function (Blueprint $table) {
            $table->dropColumn('lookable_type','qty','price','lookable_id','is_include_tax','percent_tax','tax_id','total','tax','grandtotal','note','deleted_at');
        });
    }
};
