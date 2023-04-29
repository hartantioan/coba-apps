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
        Schema::create('fund_requests', function (Blueprint $table) {
            $table->id();
            $table->string('code',155)->unique();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('place_id')->nullable();
            $table->bigInteger('department_id')->nullable();
            $table->bigInteger('account_id')->nullable();
            $table->char('type',1)->nullable();
            $table->date('post_date')->nullable();
            $table->date('required_date')->nullable();
            $table->bigInteger('currency_id')->nullable();
            $table->double('currency_rate')->nullable();
            $table->string('note')->nullable();
            $table->string('termin_note')->nullable();
            $table->char('payment_type',1)->nullable();
            $table->string('name_account')->nullable();
            $table->string('no_account')->nullable();
            $table->string('document')->nullable();
            $table->double('total')->nullable();
            $table->double('tax')->nullable();
            $table->double('wtax')->nullable();
            $table->double('grandtotal')->nullable();
            $table->char('status','1')->nullable();
            $table->bigInteger('void_id')->nullable();
            $table->string('void_note')->nullable();
            $table->timestamp('void_date')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['user_id']);
            $table->index(['place_id']);
            $table->index(['department_id']);
            $table->index(['account_id']);
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
        Schema::dropIfExists('fund_requests');
    }
};

