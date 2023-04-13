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
            $table->string('note')->nullable();
            $table->bigInteger('place_id')->nullable();
            $table->bigInteger('department_id')->nullable();
            $table->bigInteger('warehouse_id')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['good_receipt_id', 'purchase_order_detail_id', 'item_id', 'place_id', 'department_id', 'warehouse_id'],'grpodetail_index');
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
