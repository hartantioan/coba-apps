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
        Schema::create('issue_glaze_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('issue_glaze_id')->nullable();
            $table->string('lookable_type')->nullable();
            $table->bigInteger('lookable_id')->nullable();
            $table->text('note')->nullable();
            $table->decimal('qty', 20, 5);
            $table->bigInteger('place_id')->nullable();
            $table->bigInteger('warehouse_id')->nullable();
            $table->decimal('total', 20, 5);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('issue_glaze_details');
    }
};
