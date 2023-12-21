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
        Schema::create('overtime_costs', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->bigInteger('place_id')->nullable();
            $table->bigInteger('level_id')->nullable();
            $table->double('nominal')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->char('type', 1)->nullable();
            $table->char('status', 1)->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('overtime_costs');
    }
};
