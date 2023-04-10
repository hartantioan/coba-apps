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
        if(!Schema::hasTable('payment_request_details'))
        Schema::create('payment_request_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('payment_request_id')->nullable();
            $table->string('lookable_type',155)->nullable();
            $table->bigInteger('lookable_id')->nullable();
            $table->bigInteger('coa_id')->nullable();
            $table->double('nominal')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['payment_request_id']);
            $table->index(['fund_request_id']);
            $table->index(['purchase_down_payment_id']);
            $table->index(['purchase_invoice_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_request_details');
    }
};
