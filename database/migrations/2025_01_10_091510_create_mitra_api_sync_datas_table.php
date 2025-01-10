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
        Schema::create('mitra_api_sync_datas', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('mitra_id')->nullable();         // id broker/mitra tujuan
            $table->morphs('lookable');                         // (lookable_type, lookable_id). type dan id untuk link ke Model
            $table->string('operation')->nullable();            // Operation: index, store, show, update, delete. Disamakan dengan api mitra saja.
            $table->json('operation_params')->nullable();       // Parameters operation (e.g., { "force": true } for deletes, optional)
            $table->json('payload')->nullable();                // Data to sent to api
            $table->char('status',1)->nullable()->default("0"); // 0 pending, 1 success, 2 failed
            $table->integer('attempts')->nullable();            // Sync attempts yang sudah dilakukan untuk record ini
            $table->json('api_response')->nullable();           // raw response from endpoint, status & body. Nanti diganti dengan error message?
            $table->timestamps();
        });
    }   

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mitra_api_sync_datas');
    }
};
