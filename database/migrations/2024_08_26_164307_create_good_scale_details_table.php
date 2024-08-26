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
        Schema::create('good_scale_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('good_scale_id')->nullable();
            $table->string('lookable_type',155)->nullable();
            $table->bigInteger('lookable_id')->nullable();
            $table->decimal('qty',20,5)->nullable();
            $table->decimal('total',20,5)->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['good_scale_id','lookable_type','lookable_id'],'gsd_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('good_scale_details');
    }
};
