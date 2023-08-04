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
        Schema::create('attendance_temps', function (Blueprint $table) {
            $table->id();
            $table->string('code',155)->nullable();
            $table->string('user_id',155)->nullable();
            $table->tinyInteger('verify_type')->nullable();
            $table->string('record_time')->nullable();
            $table->string('machine_id',25)->nullable();
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
        Schema::dropIfExists('attendance_temps');
    }
};
