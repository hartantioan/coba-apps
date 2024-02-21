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
        Schema::create('item_buffers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('item_id')->nullable();
            $table->bigInteger('place_id')->nullable();
            $table->double('min_stock')->nullable();
            $table->double('max_stock')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['place_id', 'item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_buffers');
    }
};
