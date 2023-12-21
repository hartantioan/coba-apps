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
        Schema::create('employee_reward_punishment_payments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('period_id')->nullable();
            $table->date('post_date')->nullable();
            $table->double('nominal')->nullable();
            $table->bigInteger('employee_reward_punishment_detail_id')->nullable();

            $table->timestamps();
            $table->softDeletes('deleted_at');
            $table->char('status', 1)->nullable();
            $table->index(['user_id', 'period_id'],'mrd_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_reward_punishments_payments');
    }
};
