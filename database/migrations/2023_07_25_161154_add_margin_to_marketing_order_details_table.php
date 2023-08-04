<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('marketing_order_details', function (Blueprint $table) {
            $table->dropColumn('row_total');
            $table->double('margin')->after('price')->nullable();
            $table->char('is_include_tax',1)->after('margin')->nullable();
            $table->double('percent_tax')->after('is_include_tax')->nullable();
            $table->bigInteger('tax_id')->after('percent_tax')->nullable();
            $table->double('total')->after('price_after_discount')->nullable();
            $table->double('tax')->after('total')->nullable();
            $table->double('grandtotal')->after('tax')->nullable();
            $table->bigInteger('item_stock_id')->after('note')->nullable();
            $table->bigInteger('place_id')->after('item_stock_id')->nullable();
            $table->bigInteger('warehouse_id')->after('place_id')->nullable();
            $table->index(['marketing_order_id','item_id','tax_id','item_stock_id','place_id','warehouse_id'],'marketing_order_detail_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('marketing_order_details', function (Blueprint $table) {
            $table->dropColumn('margin','percent_tax','tax_id','is_include_tax','total','tax','grandtotal','item_stock_id','place_id','warehouse_id');
        });
    }
};
