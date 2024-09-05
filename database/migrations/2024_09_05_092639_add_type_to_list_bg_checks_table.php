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
        Schema::table('list_bg_checks', function (Blueprint $table) {
            $table->char('type',1)->nullable()->after('coa_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('list_bg_checks', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
