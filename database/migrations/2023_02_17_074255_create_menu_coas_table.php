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
        if(!Schema::hasTable('menu_coas'))
        Schema::create('menu_coas', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('menu_id')->nullable();
            $table->bigInteger('coa_id')->nullable();
            $table->bigInteger('currency_id')->nullable();
            $table->string('field_name',155)->nullable();
            $table->char('type',1)->nullable();
            $table->double('percentage')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['user_id','menu_id','coa_id','currency_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('menu_coas');
    }
};
