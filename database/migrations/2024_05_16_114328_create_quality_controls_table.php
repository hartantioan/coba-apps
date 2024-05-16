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
        Schema::create('quality_controls', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('good_scale_detail_id')->nullable();
            $table->string('name')->nullable();
            $table->decimal('nominal')->nullable();
            $table->string('unit')->nullable();
            $table->char('is_affect_qty',1)->nullable();
            $table->string('note'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quality_controls');
    }
};
