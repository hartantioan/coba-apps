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
        Schema::create('bom_calculator_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('bom_calculator_id')->nullable();
            $table->string('lookable_type',155)->nullable();
            $table->bigInteger('lookable_id')->nullable();
            $table->string('name')->nullable();
            $table->decimal('qty',20,5)->nullable();
            $table->decimal('price',20,5)->nullable();
            $table->decimal('total',20,5)->nullable();
            $table->char('group',1)->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['bom_calculator_id','lookable_type','lookable_id'],'bcd_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bom_calculator_details');
    }
};
