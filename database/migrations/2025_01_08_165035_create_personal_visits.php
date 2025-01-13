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
        Schema::create('personal_visits', function (Blueprint $table) {
            $table->id();
            $table->string('code',155)->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->string('image_in')->nullable();
            $table->text('note_in')->nullable();
            $table->string('image_out')->nullable();
            $table->text('note_out')->nullable();
            $table->timestamp('date_in')->nullable();
            $table->timestamp('date_out')->nullable();
            $table->string('location')->nullable();
            $table->string('latitude_in')->nullable();
            $table->string('longitude_in')->nullable();
            $table->string('latitude_out')->nullable();
            $table->string('longitude_out')->nullable();
            $table->char('status', 1)->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_visits');
    }
};
