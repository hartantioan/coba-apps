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
        Schema::create('production_batch_usages', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('production_batch_id')->nullable()->index();
            $table->string('lookable_type',155)->nullable()->index();
            $table->bigInteger('lookable_id')->nullable()->index();
            $table->decimal('qty',20,5)->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_batch_usages');
    }
};
