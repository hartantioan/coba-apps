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
        Schema::create('item_moves', function (Blueprint $table) {
            $table->id();
            $table->string('lookable_type',155)->nullable();
            $table->bigInteger('lookable_id')->nullable();
            $table->bigInteger('item_id')->nullable();
            $table->double('qty_in')->nullable();
            $table->double('price_in')->nullable();
            $table->double('total_in')->nullable();
            $table->double('qty_out')->nullable();
            $table->double('price_out')->nullable();
            $table->double('total_out')->nullable();
            $table->double('qty_final')->nullable();
            $table->double('price_final')->nullable();
            $table->double('total_final')->nullable();
            $table->date('date')->nullable();
            $table->char('type', 3)->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_moves');
    }
};
