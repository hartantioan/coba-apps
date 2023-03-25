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
        if(!Schema::hasTable('purchase_orders'))
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('code',155)->unique();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('supplier_id')->nullable();
            $table->char('purchasing_type', 1)->nullable();
            $table->char('shipping_type', 1)->nullable();
            $table->bigInteger('place_id')->nullable();
            $table->bigInteger('department_id')->nullable();
            $table->char('is_tax', 1)->nullable();
            $table->char('is_include_tax', 1)->nullable();
            $table->string('document_no', 155)->nullable();
            $table->string('document_po')->nullable();
            $table->double('percent_tax')->nullable();
            $table->char('payment_type', 2)->nullable();
            $table->integer('payment_term')->nullable();
            $table->bigInteger('currency')->nullable();
            $table->double('currency_rate')->nullable();
            $table->date('post_date')->nullable();
            $table->date('delivery_date')->nullable();
            $table->date('document_date')->nullable();
            $table->text('note')->nullable();
            $table->double('subtotal')->nullable();
            $table->double('discount')->nullable();
            $table->double('total')->nullable();
            $table->double('tax')->nullable();
            $table->double('grandtotal')->nullable();
            $table->char('status',1)->nullable();
            $table->bigInteger('void_id')->nullable();
            $table->string('void_note')->nullable();
            $table->timestamp('void_date')->nullable();
            $table->string('receiver_name')->nullable();
            $table->string('receiver_address')->nullable();
            $table->string('receiver_phone',50)->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['user_id', 'supplier_id', 'place_id', 'department_id','currency_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_orders');
    }
};
