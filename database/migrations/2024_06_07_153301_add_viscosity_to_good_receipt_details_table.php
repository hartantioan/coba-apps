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
        Schema::table('good_receipt_details', function (Blueprint $table) {
            $table->decimal('viscosity',20,5)->nullable()->after('water_content');
            $table->decimal('residue',20,5)->nullable()->after('viscosity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('good_receipt_details', function (Blueprint $table) {
            $table->dropColumn('viscosity','residue');
        });
    }
};
