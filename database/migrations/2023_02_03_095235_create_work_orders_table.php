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
        if(!Schema::hasTable('work_orders'))
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('branch_id')->nullable();
            $table->bigInteger('plant_id')->nullable();
            $table->bigInteger('area_id')->nullable();
            $table->bigInteger('equipment_id')->nullable();
            $table->bigInteger('activity_id')->nullable();
            $table->char('type_maintenance', 1)->nullable();
            $table->char('priority', 1)->nullable();
            $table->char('type_wo', 1)->nullable();
            $table->date('date_suggested')->nullable();
            $table->integer('estimated_fix_time')->nullable();
            $table->string('detail_issue')->nullable();
            $table->string('expected_result')->nullable();
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
        Schema::dropIfExists('work_orders');
    }
};
