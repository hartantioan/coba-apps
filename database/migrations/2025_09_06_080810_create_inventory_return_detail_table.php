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
        Schema::create('inventory_return_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('inventory_return_id')->nullable();
            $table->bigInteger('item_id')->nullable();
            $table->decimal('price',20,5)->nullable();
            $table->decimal('total',20,5)->nullable();
            $table->decimal('grandtotal',20,5)->nullable();
            $table->string('note')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_return_details');
    }
};
