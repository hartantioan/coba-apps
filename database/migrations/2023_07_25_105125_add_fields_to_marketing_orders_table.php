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
        Schema::table('marketing_orders', function (Blueprint $table) {
            $table->bigInteger('account_id')->after('place_id')->nullable();
            $table->bigInteger('company_id')->after('account_id')->nullable();
            $table->date('post_date')->after('type_sales')->nullable();
            $table->date('valid_date')->after('post_date')->nullable();
            $table->string('note',255)->after('sales_id')->nullable();
            $table->double('discount')->after('subtotal')->nullable();
            $table->double('total')->after('discount')->nullable();
            $table->double('total_after_tax')->after('tax')->nullable();
            $table->double('rounding')->after('total_after_tax')->nullable();
            $table->bigInteger('void_id')->after('status')->nullable();
            $table->string('void_note')->after('void_id')->nullable();
            $table->timestamp('void_date')->after('void_note')->nullable();
            $table->bigInteger('currency_id')->after('sales_id')->nullable();
            $table->double('currency_rate')->after('currency_id')->nullable();
            $table->string('document_no',155)->after('document')->nullable();
            $table->bigInteger('subdistrict_id')->after('city_id')->nullable();

            $table->index(['user_id','place_id','account_id','company_id','sender_id','province_id','city_id','subdistrict_id','sales_id','currency_id'],'marketing_order_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('marketing_orders', function (Blueprint $table) {
            $table->dropColumn('account_id','post_date','valid_date','note','discount','total','rounding','total_after_tax','void_id','void_note','void_date','document_no','subdistrict_id');
        });
    }
};
