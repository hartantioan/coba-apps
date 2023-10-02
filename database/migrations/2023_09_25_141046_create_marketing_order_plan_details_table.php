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
        Schema::create('marketing_order_plan_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('marketing_order_plan_id')->nullable();
            $table->bigInteger('item_id')->nullable();
            $table->double('qty')->nullable();
            $table->date('request_date')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');
            $table->index(['marketing_order_plan_id','item_id'],'mopd_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketing_order_plan_details');
    }
};
