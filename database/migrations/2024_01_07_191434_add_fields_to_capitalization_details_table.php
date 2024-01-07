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
        Schema::table('capitalization_details', function (Blueprint $table) {
            $table->bigInteger('place_id')->nullable()->after('asset_id')->index();
            $table->bigInteger('warehouse_id')->nullable()->after('place_id')->index();
            $table->bigInteger('line_id')->nullable()->after('warehouse_id')->index();
            $table->bigInteger('machine_id')->nullable()->after('line_id')->index();
            $table->bigInteger('department_id')->nullable()->after('machine_id')->index();
            $table->bigInteger('project_id')->nullable()->after('department_id')->index();
            $table->bigInteger('cost_distribution_id')->nullable()->after('project_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('capitalization_details', function (Blueprint $table) {
            $table->dropColumn('place_id','warehouse_id','line_id','machine_id','department_id','project_id','cost_distribution_id');
        });
    }
};
