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
        Schema::create('approval_template_menus', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('approval_template_id')->nullable();
            $table->bigInteger('menu_id')->nullable();
            $table->string('table_name',155)->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['approval_template_id','menu_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('approval_template_menus');
    }
};
