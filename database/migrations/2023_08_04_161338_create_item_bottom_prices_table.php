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
        Schema::create('item_bottom_prices', function (Blueprint $table) {
            $table->id();
            $table->string('code',50)->nullable();
            $table->bigInteger('item_id')->nullable();
            $table->bigInteger('place_id')->nullable();
            $table->double('nominal')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['item_id','place_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('item_bottom_prices');
    }
};
