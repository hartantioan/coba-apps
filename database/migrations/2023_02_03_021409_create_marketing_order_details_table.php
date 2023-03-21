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
        if(!Schema::hasTable('marketing_order_details'))
        Schema::create('marketing_order_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('marketing_order_id')->nullable();
            $table->bigInteger('item_id')->nullable();
            $table->double('qty')->nullable();
            $table->double('price')->nullable();
            $table->double('percent_discount_1')->nullable();
            $table->double('percent_discount_2')->nullable();
            $table->double('discount_3')->nullable();
            $table->double('other_fee')->nullable();
            $table->double('price_after_discount')->nullable();
            $table->double('row_total')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('marketing_order_details');
    }
};
