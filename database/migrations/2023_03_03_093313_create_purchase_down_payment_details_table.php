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
        if(!Schema::hasTable('purchase_down_payment_details'))
        Schema::create('purchase_down_payment_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('purchase_down_payment_id')->nullable();
            $table->bigInteger('purchase_order_id')->nullable();
            $table->double('nominal')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['purchase_order_down_payment_id', 'purchase_order_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_down_payment_details');
    }
};
