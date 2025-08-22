<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoreItemMovesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('store_item_moves', function (Blueprint $table) {
            $table->id();

            $table->string('lookable_type')->nullable();
            $table->bigInteger('lookable_id')->nullable();

            $table->bigInteger('item_id')->nullable();

            $table->decimal('qty_in', 20, 5)->nullable();
            $table->decimal('price_in', 20, 5)->nullable();
            $table->decimal('total_in', 20, 5)->nullable();

            $table->decimal('qty_out', 20, 5)->nullable();
            $table->decimal('price_out', 20, 5)->nullable();
            $table->decimal('total_out', 20, 5)->nullable();

            $table->decimal('qty_final', 20, 5)->nullable();
            $table->decimal('price_final', 20, 5)->nullable();
            $table->decimal('total_final', 20, 5)->nullable();

            $table->date('date')->nullable();
            $table->tinyInteger('type')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_item_moves');
    }
}
