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
        Schema::create('user_educations', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->nullable();
            $table->char('stage',2)->nullable();
            $table->string('code',155)->unique();
            $table->string('school_name', 155)->nullable();
            $table->string('major', 155)->nullable();
            $table->string('final_score',155)->nullable();
            $table->string('year_start',155)->nullable();
            $table->string('year_end',155)->nullable();
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
        Schema::dropIfExists('user_education');
    }
};
