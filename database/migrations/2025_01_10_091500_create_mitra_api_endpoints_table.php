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
        Schema::create('mitra_api_endpoints', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('mitra_id');                 // id broker/mitra yang bersangkutan
            $table->string('base_url')->nullable();         // base url dari broker
            $table->string('lookable_type')->nullable();    // lookable_type di mitra_api_sync
            $table->string('operation')->nullable();        // operation: index, store, show, update, delete. Disamakan dengan api mitra saja.
            $table->string('method')->nullable();           // HTTP method: get, post, put, delete 
            $table->string('endpoint')->nullable();         // endpoint dari operation
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mitra_api_endpoints');
    }
};
