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
        Schema::table('production_receive_issues', function (Blueprint $table) {
            $table->char('is_auto',1)->nullable()->after('production_issue_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_receive_issues', function (Blueprint $table) {
            $table->dropColumn('is_auto');
        });
    }
};
