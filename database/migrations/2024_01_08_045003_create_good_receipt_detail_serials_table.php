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
        Schema::create('good_receipt_detail_serials', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('good_receipt_id')->nullable()->index();
            $table->bigInteger('good_receipt_detail_id')->nullable()->index();
            $table->bigInteger('item_id')->nullable()->index();
            $table->string('serial_number',100)->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('good_receipt_detail_serials');
    }
};
