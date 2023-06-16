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
        if(!Schema::hasTable('purchase_order_details'))
        Schema::create('purchase_order_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('purchase_order_id')->nullable();
            $table->bigInteger('purchase_request_detail_id')->nullable();
            $table->bigInteger('good_issue_detail_id')->nullable();
            $table->bigInteger('item_id')->nullable();
            $table->bigInteger('coa_id')->nullable();
            $table->double('qty')->nullable();
            $table->double('price')->nullable();
            $table->double('percent_discount_1')->nullable();
            $table->double('percent_discount_2')->nullable();
            $table->double('discount_3')->nullable();
            $table->double('subtotal')->nullable();
            $table->string('note')->nullable();
            $table->string('note2')->nullable();
            $table->char('is_tax',1)->nullable();
            $table->char('is_include_tax',1)->nullable();
            $table->double('percent_tax')->nullable();
            $table->char('is_wtax',1)->nullable();
            $table->double('percent_tax')->nullable();
            $table->bigInteger('tax_id')->nullable();
            $table->bigInteger('wtax_id')->nullable();
            $table->bigInteger('place_id')->nullable();
            $table->bigInteger('line_id')->nullable();
            $table->bigInteger('machine_id')->nullable();
            $table->bigInteger('department_id')->nullable();
            $table->bigInteger('warehouse_id')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['purchase_order_id', 'item_id', 'purchase_request_detail_id', 'good_issue_detail_id', 'place_id', 'line_id', 'machine_id', 'department_id', 'warehouse_id','tax_id','wtax_id','coa_id'],'podt_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_order_details');
    }
};
