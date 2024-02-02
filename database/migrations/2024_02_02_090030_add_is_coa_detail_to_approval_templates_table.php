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
        Schema::table('approval_templates', function (Blueprint $table) {
            $table->char('is_coa_detail',1)->nullable()->after('is_check_benchmark');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approval_templates', function (Blueprint $table) {
            $table->dropColumn('is_coa_detail');
        });
    }
};
