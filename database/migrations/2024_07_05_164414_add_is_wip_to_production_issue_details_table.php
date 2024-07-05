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
            $table->char('is_wip',1)->nullable()->after('from_item_stock_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_issue_details', function (Blueprint $table) {
            $table->dropColumn('is_wip');
        });
    }
};
