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
        Schema::create('shift_request_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('shift_request_id')->nullable();
            $table->bigInteger('employee_schedule_id')->nullable();
            $table->bigInteger('shift_id')->nullable();
            $table->date('date')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_request_details');
    }
};
