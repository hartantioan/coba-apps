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
        Schema::create('complaint_sale_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('complaint_sales_id')->nullable();
            $table->string('lookable_type',155)->nullable();
            $table->bigInteger('lookable_id')->nullable();
            $table->decimal('qty_color_mistake',20,5)->nullable();
            $table->decimal('qty_motif_mistake',20,5)->nullable();
            $table->decimal('qty_size_mistake',20,5)->nullable();
            $table->decimal('qty_broken',20,5)->nullable();
            $table->decimal('qty_mistake',20,5)->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaint_sale_details');
    }
};
