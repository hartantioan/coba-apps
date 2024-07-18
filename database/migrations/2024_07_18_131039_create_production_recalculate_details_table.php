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
        Schema::create('production_recalculate_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('production_recalculate_id')->nullable();
            $table->string('lookable_type',155)->nullable();
            $table->bigInteger('lookable_id')->nullable();
            $table->bigInteger('production_batch_id')->nullable();
            $table->bigInteger('resource_id')->nullable();
            $table->decimal('total',20,5)->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['production_recalculate_id','lookable_type','lookable_id','production_batch_id','resource_id'],'prrd_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_recalculate_details');
    }
};
