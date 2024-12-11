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
        Schema::create('truck_queue_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('truck_queue_id')->nullable();
            $table->bigInteger('good_scale_id')->nullable();
            $table->bigInteger('marketing_delivery_oder_process_id')->nullable();
            $table->dateTime('time_in')->nullable();
            $table->softDeletes('deleted_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('truck_queue_details');
    }
};
