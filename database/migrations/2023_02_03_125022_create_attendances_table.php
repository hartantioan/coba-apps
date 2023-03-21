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
        if(!Schema::hasTable('attendances'))
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->date('date')->nullable();
            $table->bigInteger('shift_employee_id')->nullable();
            $table->time('in_time')->nullable();
            $table->time('out_time')->nullable();
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
        Schema::dropIfExists('attendances');
    }
};
