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
        Schema::create('currency_dates', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('currency_id')->nullable();
            $table->date('currency_date')->nullable();
            $table->decimal('currency_rate',20,5)->nullable();
            $table->string('taken_from',500)->nullable();
            $table->softDeletes('deleted_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currency_dates');
    }
};
