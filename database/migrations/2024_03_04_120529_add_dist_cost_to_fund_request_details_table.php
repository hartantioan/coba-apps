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
        Schema::table('fund_request_details', function (Blueprint $table) {
            $table->bigInteger('place_id')->nullable()->after('grandtotal')->index();
            $table->bigInteger('line_id')->nullable()->after('place_id')->index();
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
        Schema::table('fund_request_details', function (Blueprint $table) {
            $table->dropColumn('place_id','line_id','machine_id','division_id','project_id');
        });
    }
};
