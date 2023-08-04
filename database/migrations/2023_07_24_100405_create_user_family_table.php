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
        Schema::create('user_families', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->nullable();
            $table->string('code',155)->unique();
            $table->string('name', 155)->nullable();
            $table->char('relation', 2)->nullable();
            $table->string('emergency_contact', 155)->nullable();
            $table->string('address', 155)->nullable();
            $table->string('id_number',155)->nullable();
            $table->char('marriage_status',1)->nullable();
            $table->char('religion',1)->nullable();
            $table->string('job',155)->nullable();
            $table->date('birth_date')->nullable();
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
        Schema::dropIfExists('user_family');
    }
};
