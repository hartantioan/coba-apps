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
        if(!Schema::hasTable('journals'))
        Schema::create('journals', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('account_id')->nullable();
            $table->string('code', 50)->unique();
            $table->bigInteger('place_id')->nullable();
            $table->string('lookable_type')->nullable();
            $table->bigInteger('lookable_id')->nullable();
            $table->bigInteger('currency_id')->nullable();
            $table->double('currency_rate')->nullable();
            $table->date('post_date')->nullable();
            $table->date('due_date')->nullable();
            $table->text('note')->nullable();
            $table->char('status',1)->nullable();
            $table->bigInteger('void_id')->nullable();
            $table->string('void_note')->nullable();
            $table->timestamp('void_date')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['user_id', 'place_id', 'account_id', 'lookable_type', 'lookable_id', 'currency_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('journals');
    }
};
