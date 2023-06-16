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
        if(!Schema::hasTable('outgoing_payments'))
        Schema::create('outgoing_payments', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('company_id')->nullable();
            $table->bigInteger('account_id')->nullable();
            $table->bigInteger('payment_request_id')->nullable();
            $table->bigInteger('coa_source_id')->nullable();
            $table->bigInteger('currency_id')->nullable();
            $table->double('currency_rate')->nullable();
            $table->date('post_date')->nullable();
            $table->date('pay_date')->nullable();
            $table->bigInteger('cost_distribution_id')->nullable();
            $table->double('admin')->nullable();
            $table->double('grandtotal')->nullable();
            $table->string('document')->nullable();
            $table->text('note')->nullable();
            $table->char('status', 1)->nullable();
            $table->bigInteger('void_id')->nullable();
            $table->string('void_note')->nullable();
            $table->timestamp('void_date')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['user_id','account_id','company_id','coa_source_id','currency_id','payment_request_id','cost_distribution_id'],'outgoing_payment_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('outgoing_payments');
    }
};
