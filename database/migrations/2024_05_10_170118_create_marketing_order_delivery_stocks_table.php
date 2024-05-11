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
        Schema::create('marketing_order_delivery_stocks', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('marketing_order_detail_id')->nullable()->index();
            $table->bigInteger('item_stock_id')->nullable()->index();
            $table->decimal('qty',20,5)->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketing_order_delivery_stocks');
    }
};
