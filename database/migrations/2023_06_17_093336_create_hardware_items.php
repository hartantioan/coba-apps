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
        Schema::create('hardware_items', function (Blueprint $table) {
            $table->id();
            $table->string('code',155)->unique();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('item_id')->nullable();
            $table->bigInteger('hardware_item_group_id')->nullable();
            $table->bigInteger('currency_id')->nullable();
            $table->string('info')->nullable();
            $table->string('location')->nullable();
            $table->string('ip_address')->nullable();
            $table->double('nominal')->nullable();
            $table->char('status', 1)->nullable();
            $table->softDeletes('deleted_at'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hardware_items');
    }
};
