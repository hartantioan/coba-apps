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
            $table->bigInteger('place_id')->nullable()->after('from_item_stock_id')->index();
            $table->bigInteger('warehouse_id')->nullable()->after('place_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_issue_details', function (Blueprint $table) {
            $table->dropColumn('place_id','warehouse_id');
        });
    }
};
