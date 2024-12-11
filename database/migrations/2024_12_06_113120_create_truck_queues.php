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
        Schema::create('truck_queues', function (Blueprint $table) {
            $table->id();
            $table->string('code',155)->nullable();
            $table->string('name')->nullable();
            $table->string('no_pol')->nullable();
            $table->string('truck')->nullable();
            $table->char('document_status',1)->nullable();
            $table->string('code_barcode')->nullable();
            $table->dateTime('date')->nullable();
            $table->softDeletes('deleted_at');
            $table->char('status', 1)->nullable();
            $table->char('type', 1)->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('truck_queues');
    }
};
