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
        Schema::create('mitra_price_lists', function (Blueprint $table) {
            $table->id();
            $table->string('sales_area_code')->nullable();
            $table->bigInteger('variety_id')->nullable();
            $table->bigInteger('type_id')->nullable();
            $table->bigInteger('package_id')->nullable();
            $table->timestamp('effective_date')->nullable();
            $table->bigInteger('uom_id')->nullable();
            $table->mediumInteger('min_qty')->nullable();
            $table->decimal('price_exclude',20,5)->nullable();
            $table->decimal('price_include',20,5)->nullable();
            
            $table->bigInteger('mitra_id')->nullable()->comment('ID Broker');
            $table->string('price_group_code')->nullable();
            $table->char('status', 1)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mitra_price_lists');
    }
};
