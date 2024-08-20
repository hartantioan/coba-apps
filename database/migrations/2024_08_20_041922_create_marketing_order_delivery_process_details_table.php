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
        Schema::create('marketing_order_delivery_process_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('marketing_order_delivery_process_id')->nullable();
            $table->bigInteger('marketing_order_delivery_detail_id')->nullable();
            $table->bigInteger('item_stock_id')->nullable();
            $table->decimal('qty',20,5)->nullable();
            $table->decimal('total',20,5)->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');
            $table->index(['marketing_order_delivery_process_id','marketing_order_delivery_detail_id','item_stock_id'],'modpd_detail_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketing_order_delivery_process_details');
    }
};
