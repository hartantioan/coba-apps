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
            $table->string('batch_no',155)->nullable()->after('from_item_stock_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_issue_receive_details', function (Blueprint $table) {
            $table->dropColumn('batch_no');
        });
    }
};
