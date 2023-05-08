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
        Schema::create('approval_template_stages', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('approval_template_id')->nullable();
            $table->bigInteger('approval_stage_id')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['approval_template_id','stage_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('approval_template_stages');
    }
};
