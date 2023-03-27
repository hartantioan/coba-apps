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
        if(!Schema::hasTable('good_receipts'))
        Schema::create('good_receipts', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('good_receipt_main_id')->nullable();
            $table->bigInteger('purchase_order_id')->nullable();
            $table->bigInteger('account_id')->nullable();
            $table->bigInteger('company_id')->nullable();
            $table->bigInteger('place_id')->nullable();
            $table->bigInteger('department_id')->nullable();
            $table->bigInteger('currency_id')->nullable();
            $table->double('currency_rate')->nullable();
            $table->double('total')->nullable();
            $table->double('tax')->nullable();
            $table->double('grandtotal')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['account_id', 'purchase_order_id', 'company_id', 'place_id', 'department_id','currency_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('good_receipts');
    }
};
