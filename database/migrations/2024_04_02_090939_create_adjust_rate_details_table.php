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
        Schema::create('adjust_rate_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('adjust_rate_id')->nullable();
            $table->string('lookable_type',155)->nullable();
            $table->bigInteger('lookable_id')->nullable();
            $table->decimal('nominal',20,5)->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');
            $table->index(['adjust_rate_id','lookable_id'],'ard_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adjust_rate_details');
    }
};
