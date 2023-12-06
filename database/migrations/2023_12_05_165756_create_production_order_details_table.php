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
        Schema::create('production_order_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('production_order_id')->nullable();
            $table->bigInteger('bom_detail_id')->nullable();
            $table->string('lookable_type')->nullable();
            $table->bigInteger('lookable_id')->nullable();
            $table->double('qty')->nullable();
            $table->double('qty_real')->nullable();
            $table->double('nominal')->nullable();
            $table->double('nominal_real')->nullable();
            $table->double('total')->nullable();
            $table->double('total_real')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');
            $table->index(['production_order_id','bom_detail_id','lookable_id'],'pordd_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_order_details');
    }
};
