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
        if(!Schema::hasTable('marketing_order_invoices'))
        Schema::create('marketing_order_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('customer_id')->nullable();
            $table->bigInteger('branch_id')->nullable();
            $table->date('posting_date')->nullable();
            $table->date('due_date')->nullable();
            $table->date('document_date')->nullable();
            $table->char('status', 1)->nullable();
            $table->char('type', 1)->nullable();
            $table->double('subtotal')->nullable();
            $table->double('percent_discount')->nullable();
            $table->double('nominal_discount')->nullable();
            $table->double('total')->nullable();
            $table->double('tax')->nullable();
            $table->double('grandtotal')->nullable();
            $table->string('document')->nullable();
            $table->text('note')->nullable();
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
        Schema::dropIfExists('marketing_order_invoices');
    }
};
