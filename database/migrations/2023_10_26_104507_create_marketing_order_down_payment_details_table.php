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
        Schema::create('marketing_order_down_payment_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('marketing_order_down_payment_id')->nullable();
            $table->bigInteger('marketing_order_id')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');
            $table->index(['marketing_order_down_payment_id','marketing_order_id'],'modpd_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketing_order_down_payment_details');
    }
};
