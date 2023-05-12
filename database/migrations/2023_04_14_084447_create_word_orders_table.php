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
        if (!Schema::hasTable('work_orders')) {
            Schema::create('work_orders', function (Blueprint $table) {
                $table->id();
                $table->string('code',155)->unique();
                $table->bigInteger('place_id')->nullable();
                $table->bigInteger('equipment_id')->nullable();
                $table->bigInteger('activity_id')->nullable();
                $table->bigInteger('area_id')->nullable();
                $table->bigInteger('user_id')->nullable();
                $table->char('maintenance_type',1)->nullable();
                $table->char('priority',1)->nullable();
                $table->char('work_order_type',1)->nullable();
                $table->date('suggested_completion_date')->nullable();
                $table->date('request_date')->nullable();
                $table->bigInteger('estimated_fix_time')->nullable();
                $table->text('detail_issue')->nullable();
                $table->text('expected_result')->nullable();
                $table->char('status', 1)->nullable();
                $table->bigInteger('void_id')->nullable();
                $table->string('void_note',155)->nullable();
                $table->date('void_date')->nullable();
                $table->date('actual_start')->nullable();
                $table->date('actual_finish')->nullable();
                $table->timestamps();
                $table->softDeletes('deleted_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('maintenances');
    }
};
