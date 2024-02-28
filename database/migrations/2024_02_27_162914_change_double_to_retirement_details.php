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
        Schema::table('retirement_details', function (Blueprint $table) {
            $table->decimal('qty', 20, 5)->change();
            $table->decimal('retirement_nominal', 20, 5)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('retirement_details', function (Blueprint $table) {
            //
        });
    }
};
