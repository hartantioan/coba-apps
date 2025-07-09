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
        Schema::create('delivery_receive_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('delivery_receive_id')->nullable();
            $table->bigInteger('item_id')->nullable();
            $table->decimal('qty',20,5)->nullable();
            $table->decimal('price',20,5)->nullable();
            $table->decimal('total',20,5)->nullable();
            $table->decimal('tax',20,5)->nullable();
            $table->decimal('wtax',20,5)->nullable();
            $table->decimal('grandtotal',20,5)->nullable();
            $table->string('note')->nullable();
            $table->string('remark')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_receive_details');
    }
};
