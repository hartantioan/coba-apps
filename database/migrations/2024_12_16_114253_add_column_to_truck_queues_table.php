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
        Schema::table('truck_queues', function (Blueprint $table) {
            $table->string('note')->nullable();
            $table->string('no_container')->nullable();
            $table->dateTime('change_status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('truck_queues', function (Blueprint $table) {
            //
        });
    }
};
