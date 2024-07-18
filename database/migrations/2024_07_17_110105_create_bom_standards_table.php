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
        Schema::create('bom_standards', function (Blueprint $table) {
            $table->id();
            $table->string('code',155)->nullable();
            $table->string('name')->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->char('status',1)->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bom_standards');
    }
};
