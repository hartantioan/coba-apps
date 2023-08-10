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
        Schema::create('marketing_order_delivery_processes', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->nullable();
            $table->string('code',155)->unique();
            $table->bigInteger('company_id')->nullable();
            $table->bigInteger('account_id')->nullable();
            $table->bigInteger('marketing_order_delivery_id')->nullable();
            $table->date('post_date')->nullable();
            $table->string('document')->nullable();
            $table->string('note')->nullable();
            $table->char('status',1)->nullable();
            $table->double('total')->nullable();
            $table->double('tax')->nullable();
            $table->double('grandtotal')->nullable();
            $table->bigInteger('void_id')->nullable();
            $table->string('void_note')->nullable();
            $table->timestamp('void_date')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['user_id','account_id','company_id','marketing_order_delivery_id'],'delivery_order_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('marketing_order_delivery_processes');
    }
};
