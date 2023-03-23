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
        Schema::create('used_datas', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->string('lookable_type',155)->nullable();
            $table->bigInteger('lookable_id')->nullable();
            $table->string('ref')->nullable();
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
        Schema::dropIfExists('used_datas');
    }
};
