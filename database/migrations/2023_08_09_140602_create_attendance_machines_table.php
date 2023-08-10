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
        Schema::create('attendance_machines', function (Blueprint $table) {
            $table->id();
            $table->string('code',155)->nullable();
            $table->string('name')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('port')->nullable();
            $table->string('location')->nullable();
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
        Schema::dropIfExists('attendance_machines');
    }
};
