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
        Schema::create('user_date_menus', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_date_id')->nullable();
            $table->bigInteger('menu_id')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['user_date_id','menu_id'],'user_date_menu_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_date_menus');
    }
};
