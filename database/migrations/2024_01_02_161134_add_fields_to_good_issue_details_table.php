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
        Schema::table('good_issue_details', function (Blueprint $table) {
            $table->bigInteger('place_id')->nullable()->after('lookable_id')->index();
            $table->bigInteger('warehouse_id')->nullable()->after('place_id')->index();
            $table->bigInteger('area_id')->nullable()->after('warehouse_id')->index();
            $table->bigInteger('item_shading_id')->nullable()->after('area_id')->index();
            $table->bigInteger('line_id')->nullable()->after('item_shading_id')->index();
            $table->bigInteger('machine_id')->nullable()->after('line_id')->index();
            $table->bigInteger('department_id')->nullable()->after('machine_id')->index();
            $table->bigInteger('project_id')->nullable()->after('department_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('good_issue_details', function (Blueprint $table) {
            $table->dropColumn('place_id','warehouse_id','area_id','line_id','machine_id','department_id','project_id');
        });
    }
};
