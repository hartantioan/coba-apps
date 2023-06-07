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
        if(!Schema::hasTable('approval_matrixs'))
        Schema::create('approval_matrixs', function (Blueprint $table) {
            $table->id();
            $table->string('code',155)->unique();
            $table->bigInteger('approval_template_stage_id')->nullable();
            $table->bigInteger('approval_source_id')->nullable();
            $table->string('note')->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->dateTime('date_request')->nullable();
            $table->dateTime('date_process')->nullable();
            $table->char('approved',1)->nullable();
            $table->char('rejected',1)->nullable();
            $table->char('revised',1)->nullable();
            $table->char('status',1)->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['approval_template_stage_id', 'approval_source_id', 'user_id', 'status'],'approval_matrix_indexes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('approval_matrixs');
    }
};
