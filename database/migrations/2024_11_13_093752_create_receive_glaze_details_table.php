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
        Schema::create('receive_glaze_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('receive_glaze_id')->nullable();
            $table->bigInteger('issue_glaze_id')->nullable();
            $table->decimal('qty', 20, 5)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receive_glaze_details');
    }
};
