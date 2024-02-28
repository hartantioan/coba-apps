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
        Schema::table('outlet_price_details', function (Blueprint $table) {
            
            $table->decimal('price', 20, 5)->change();
            $table->decimal('margin', 20, 5)->change();
           
            $table->decimal('percent_discount_1', 20, 5)->change();
            $table->decimal('percent_discount_2', 20, 5)->change();
            $table->decimal('discount_3', 20, 5)->change();
            $table->decimal('final_price', 20, 5)->change();
           
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('outlet_price_details', function (Blueprint $table) {
            //
        });
    }
};
