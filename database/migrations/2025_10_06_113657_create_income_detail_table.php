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
        Schema::create('income_details', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->bigInteger('income_id')->nullable();
            $table->string('note')->nullable();
            $table->decimal('total', 20,5)->default(0)->nullable();
            $table->softDeletes(); // deleted_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('income_details');
    }
};
