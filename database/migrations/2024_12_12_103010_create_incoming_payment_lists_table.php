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
        Schema::create('incoming_payment_lists', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('incoming_payment_id')->nullable()->index();
            $table->bigInteger('list_bg_check_id')->nullable()->index();
            $table->timestamps();
            $table->softDeletes('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incoming_payment_lists');
    }
};
