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
        Schema::table('reception_hardware_items_usages', function (Blueprint $table) {
            $table->date('reception_date')->nullable();
            $table->date('return_date')->nullable();
            $table->string('return_note')->nullable();
            $table->bigInteger('user_return')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reception_hardware_items_usages', function (Blueprint $table) {
            //
        });
    }
};
