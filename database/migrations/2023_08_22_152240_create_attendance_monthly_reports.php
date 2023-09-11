<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attendance_monthly_reports', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('period_id')->nullable();
            $table->string('effective_day')->nullable();
            $table->string('arrived_on_time')->nullable();
            $table->string('absent')->nullable();
            $table->string('special_occasion')->nullable();
            $table->string('sick')->nullable();
            $table->string('t1')->nullable();
            $table->string('t2')->nullable();
            $table->string('t3')->nullable();
            $table->string('t4')->nullable();
            $table->string('outstation')->nullable();
            $table->string('furlough')->nullable();
            $table->string('dispen')->nullable();
            $table->string('alpha')->nullable();
            $table->string('wfh')->nullable();
            $table->string('out_on_time')->nullable();
            $table->string('out_log_forget')->nullable();
            $table->string('arrived_forget')->nullable();
            $table->softDeletes('deleted_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_monthly_reports');
    }
};
