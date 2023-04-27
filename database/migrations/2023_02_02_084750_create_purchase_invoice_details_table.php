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
        if(!Schema::hasTable('purchase_invoice_details'))
        Schema::create('purchase_invoice_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('purchase_invoice_id')->nullable();
            $table->bigInteger('good_receipt_id')->nullable();
            $table->bigInteger('landed_cost_id')->nullable();
            $table->bigInteger('purchase_order_id')->nullable();
            $table->double('total')->nullable();
            $table->bigInteger('tax_id')->nullable();
            $table->bigInteger('wtax_id')->nullable();
            $table->char('is_include_tax',1)->nullable();
            $table->double('percent_tax')->nullable();
            $table->double('tax')->nullable();
            $table->double('percent_wtax')->nullable();
            $table->double('wtax')->nullable();
            $table->double('grandtotal')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['purchase_invoice_id', 'landed_cost_id', 'good_receipt_id', 'purchase_order_id', 'tax_id', 'wtax_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_invoice_details');
    }
};
