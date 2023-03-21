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
        if(!Schema::hasTable('crud_positions'))
        Schema::create('crud_positions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('menu_id')->nullable();
            $table->bigInteger('position_id')->nullable();
            $table->string('operation', 10)->nullable();
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
        Schema::dropIfExists('crud_positions');
    }
};
