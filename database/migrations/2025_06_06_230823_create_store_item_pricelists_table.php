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
        Schema::create('store_item_pricelists', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('item_id')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('price', 20, 5)->nullable();
            $table->decimal('discount', 20, 5)->nullable();
            $table->decimal('sell_price', 20, 5)->nullable();
            $table->string('status')->nullable();

            $table->timestamps();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_item_pricelists');
    }
};
