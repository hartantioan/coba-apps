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
        Schema::create('overtime_requests', function (Blueprint $table) {
            $table->string('code',155)->unique();
            $table->bigInteger('shift_id')->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->time('time_in')->nullable();
            $table->time('time_out')->nullable();
            $table->date('date')->nullable();
            $table->char('status', 1)->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');
            $table->index(['user_id','shift_id'],'mrd_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('overtime_requests');
    }
};
