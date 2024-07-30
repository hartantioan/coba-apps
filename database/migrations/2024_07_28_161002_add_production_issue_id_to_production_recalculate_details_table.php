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
        Schema::table('production_recalculate_details', function (Blueprint $table) {
            $table->bigInteger('production_issue_id')->nullable()->after('lookable_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_recalculate_details', function (Blueprint $table) {
            $table->dropColumn('production_issue_id');
        });
    }
};
