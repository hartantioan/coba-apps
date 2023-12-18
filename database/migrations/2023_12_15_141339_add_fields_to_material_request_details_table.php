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
        Schema::table('material_request_details', function (Blueprint $table) {
            $table->bigInteger('line_id')->nullable()->after('place_id')->index();
            $table->bigInteger('machine_id')->nullable()->after('line_id')->index();
            $table->bigInteger('department_id')->nullable()->after('machine_id')->index();
            $table->string('requester',155)->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material_request_details', function (Blueprint $table) {
            $table->dropColumn('line_id','machine_id','department_id','requester');
        });
    }
};
