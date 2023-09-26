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
        Schema::create('production_schedule_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('production_schedule_id')->nullable();
            $table->bigInteger('shift_id')->nullable();
            $table->bigInteger('marketing_order_plan_detail_id')->nullable();
            $table->bigInteger('item_id')->nullable();
            $table->double('qty')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['production_schedule_id','shift_id','marketing_order_plan_detail_id','item_id'],'pst_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_schedule_details');
    }
};
