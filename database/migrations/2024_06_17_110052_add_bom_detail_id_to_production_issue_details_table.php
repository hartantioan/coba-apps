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
        Schema::table('production_issue_details', function (Blueprint $table) {
            $table->bigInteger('bom_detail_id')->nullable()->after('bom_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_issue_details', function (Blueprint $table) {
            $table->dropColumn('bom_detail_id');
        });
    }
};
