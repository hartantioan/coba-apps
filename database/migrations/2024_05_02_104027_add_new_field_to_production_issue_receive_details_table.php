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
            $table->bigInteger('place_id')->nullable()->after('batch_no')->index();
            $table->bigInteger('line_id')->nullable()->after('place_id')->index();
            $table->bigInteger('warehouse_id')->nullable()->after('line_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_issue_receive_details', function (Blueprint $table) {
            $table->dropColumn('place_id','line_id','warehouse_id');
        });
    }
};
