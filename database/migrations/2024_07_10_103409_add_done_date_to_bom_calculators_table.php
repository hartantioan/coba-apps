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
        Schema::table('bom_calculators', function (Blueprint $table) {
            $table->timestamp('done_date')->nullable()->after('done_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bom_calculators', function (Blueprint $table) {
            $table->dropColumn('done_date');
        });
    }
};
