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
        Schema::create('marketing_order_handover_receipt_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('marketing_order_handover_receipt_id');
            $table->bigInteger('marketing_order_receipt_id')->nullable();
            $table->char('status',1)->nullable();
            $table->string('note')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');
            $table->index(['marketing_order_handover_receipt_id','marketing_order_receipt_id'],'mohr_detail_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketing_order_handover_receipt_details');
    }
};
