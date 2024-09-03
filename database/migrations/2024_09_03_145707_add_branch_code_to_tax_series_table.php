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
        Schema::table('tax_series', function (Blueprint $table) {
            $table->char('branch_code',5)->nullable()->after('year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tax_series', function (Blueprint $table) {
            $table->dropColumn('branch_code');
        });
    }
};
