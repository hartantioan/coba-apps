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
        if(!Schema::hasTable('payment_requests'))
        Schema::create('payment_requests', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('account_id')->nullable();
            $table->bigInteger('place_id')->nullable();
            $table->bigInteger('coa_source_id')->nullable();
            $table->date('post_date')->nullable();
            $table->date('due_date')->nullable();
            $table->date('pay_date')->nullable();
            $table->bigInteger('currency_id')->nullable();
            $table->double('currency_rate')->nullable();
            $table->double('admin')->nullable();
            $table->double('grandtotal')->nullable();
            $table->string('document')->nullable();
            $table->string('account_bank',155)->nullable();
            $table->string('account_no',155)->nullable();
            $table->string('account_name',155)->nullable();
            $table->text('note')->nullable();
            $table->char('status',1)->nullable();
            $table->bigInteger('void_id')->nullable();
            $table->string('void_note')->nullable();
            $table->timestamp('void_date')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['user_id']);
            $table->index(['account_id']);
            $table->index(['place_id']);
            $table->index(['coa_source_id']);
            $table->index(['currency_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_requests');
    }
};
