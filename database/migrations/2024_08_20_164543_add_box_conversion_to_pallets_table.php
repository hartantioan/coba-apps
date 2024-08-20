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
        Schema::table('pallets', function (Blueprint $table) {
            $table->decimal('box_conversion',20,5)->nullable()->after('nominal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pallets', function (Blueprint $table) {
            $table->dropColumn('box_conversion');
        });
    }
};
