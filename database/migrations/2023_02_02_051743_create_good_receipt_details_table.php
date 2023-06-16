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
        if(!Schema::hasTable('good_receipt_details'))
        Schema::create('good_receipt_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('good_receipt_id')->nullable();
            $table->bigInteger('purchase_order_detail_id')->nullable();
            $table->bigInteger('item_id')->nullable();
            $table->double('qty')->nullable();
            $table->double('total')->nullable();
            $table->double('tax')->nullable();
            $table->double('wtax')->nullable();
            $table->double('grandtotal')->nullable();
            $table->string('note')->nullable();
            $table->string('note2')->nullable();
            $table->string('remark')->nullable();
            $table->bigInteger('place_id')->nullable();
            $table->bigInteger('line_id')->nullable();
            $table->bigInteger('machine_id')->nullable();
            $table->bigInteger('department_id')->nullable();
            $table->bigInteger('warehouse_id')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['good_receipt_id', 'purchase_order_detail_id', 'item_id', 'place_id', 'line_id', 'machine_id', 'department_id', 'warehouse_id'],'grpodetail_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('good_receipt_details');
    }
};
