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
        Schema::create('user_specials', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->bigInteger('user_id')->nullable();
            $table->char('type',1)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->char('status',2)->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');
           
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_specials');
    }
};
