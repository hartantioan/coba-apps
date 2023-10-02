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
        Schema::create('production_schedule_targets', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('production_schedule_id')->nullable();
            $table->bigInteger('marketing_order_plan_detail_id')->nullable();
            $table->double('qty')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_schedule_targets');
    }
};
