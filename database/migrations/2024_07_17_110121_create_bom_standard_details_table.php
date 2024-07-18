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
        Schema::create('bom_standard_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('bom_standard_id')->nullable();
            $table->string('lookable_type',155)->nullable();
            $table->bigInteger('lookable_id')->nullable();
            $table->bigInteger('cost_distribution_id')->nullable();
            $table->decimal('qty',20,5)->nullable();
            $table->decimal('nominal',20,5)->nullable();
            $table->decimal('total',20,5)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['bom_standard_id','lookable_type','lookable_id','cost_distribution_id'],'bsd_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bom_standard_details');
    }
};
