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
        Schema::create('marketing_order_delivery_process_tracks', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('marketing_order_delivery_process_id')->nullable();
            $table->string('note')->nullable();
            $table->char('status',1)->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['user_id','marketing_order_delivery_process_id'],'delivery_order_tracks_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketing_order_delivery_process_tracks');
    }
};
