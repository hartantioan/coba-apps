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
        Schema::create('employee_reward_punishment_details', function (Blueprint $table) {
            $table->id();
            $table->double('nominal_payment')->nullable();
            $table->integer('instalment')->nullable();
            $table->double('nominal_total')->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('employee_reward_punishment_id')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');
            $table->char('status', 1)->nullable();
            $table->index(['employee_reward_punishment_id', 'user_id'],'mrd_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_reward_punishment_details');
    }
};
