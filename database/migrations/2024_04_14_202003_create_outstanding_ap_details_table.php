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
        Schema::create('outstanding_ap_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('outstanding_ap_id')->nullable();
            $table->string('code',50)->nullable();
            $table->string('account',155)->nullable();
            $table->date('post_date')->nullable();
            $table->date('received_date')->nullable();
            $table->integer('top')->nullable();
            $table->date('due_date')->nullable();
            $table->decimal('total',20,5)->nullable();
            $table->decimal('paid',20,5)->nullable();
            $table->decimal('balance',20,5)->nullable();
            $table->timestamps();
            $table->index(['outstanding_ap_id'],'opd_detail');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outstanding_ap_details');
    }
};
