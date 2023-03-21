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
        if(!Schema::hasTable('menus'))
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('name', 155)->nullable();
            $table->string('url')->nullable();
            $table->string('icon')->nullable();
            $table->string('table_name',155)->nullable();
            $table->bigInteger('parent_id')->nullable();
            $table->integer('order')->nullable();
            $table->char('status', 1)->nullable();
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
        Schema::dropIfExists('menus');
    }
};
