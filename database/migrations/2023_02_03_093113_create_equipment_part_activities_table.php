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
        if(!Schema::hasTable('equipment_part_activities'))
        Schema::create('equipment_part_activities', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('equipment_part_id')->nullable();
            $table->bigInteger('activity_id')->nullable();
            $table->date('date_last_maintenance')->nullable();
            $table->integer('repair_period')->nullable();
            $table->char('priority', 1)->nullable();
            $table->string('summary_activity')->nullable();
            $table->string('detail_activity')->nullable();
            $table->string('expected_result')->nullable();
            $table->char('status', 1)->nullable();
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
        Schema::dropIfExists('equipment_part_activities');
    }
};
