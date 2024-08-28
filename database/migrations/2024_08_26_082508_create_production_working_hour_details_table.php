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
        Schema::create('production_working_hour_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('production_working_hour_id')->nullable()->index();
            $table->char('type',1)->nullable();
            $table->text('note')->nullable();
            $table->text('working_hour')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_working_hour_details');
    }
};
