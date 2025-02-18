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
        Schema::table('assets', function (Blueprint $table) {
            $table->bigInteger('cost_distribution_id')->nullable()->after('hardware_item_id')->index();
            $table->bigInteger('line_id')->nullable()->after('cost_distribution_id')->index();
            $table->bigInteger('machine_id')->nullable()->after('line_id')->index();
            $table->bigInteger('division_id')->nullable()->after('machine_id')->index();
            $table->bigInteger('project_id')->nullable()->after('division_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn('cost_distribution_id','line_id','machine_id','division_id','project_id');
        });
    }
};
