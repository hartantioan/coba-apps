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
        Schema::create('production_barcode_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('production_barcode_id')->nullable()->index();
            $table->bigInteger('item_id')->nullable()->index();
            $table->bigInteger('bom_id')->nullable()->index();
            $table->bigInteger('item_unit_id')->nullable()->index();
            $table->string('pallet_no',155)->nullable();
            $table->string('shading',155)->nullable();
            $table->decimal('qty_sell',20,5)->nullable();
            $table->decimal('qty',20,5)->nullable();
            $table->decimal('conversion',20,5)->nullable();
            $table->bigInteger('pallet_id')->nullable()->index();
            $table->bigInteger('grade_id')->nullable()->index();
            $table->timestamps();
            $table->softDeletes('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_barcode_details');
    }
};
