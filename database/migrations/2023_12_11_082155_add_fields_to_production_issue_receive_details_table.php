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
        Schema::table('production_issue_receive_details', function (Blueprint $table) {
            $table->string('shading',50)->nullable()->after('lookable_id');
            $table->bigInteger('from_item_stock_id')->nullable()->after('type')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_issue_receive_details', function (Blueprint $table) {
            $table->dropColumn('from_item_stock_id');
        });
    }
};
