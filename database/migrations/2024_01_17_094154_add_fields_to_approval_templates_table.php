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
            $table->char('is_check_benchmark',1)->nullable()->after('is_check_nominal');
            $table->char('nominal_type',1)->nullable()->after('is_check_benchmark');
            $table->double('nominal_final')->nullable()->after('nominal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approval_templates', function (Blueprint $table) {
            $table->dropColumn('is_check_benchmark','nominal_type','nominal_final');
        });
    }
};
