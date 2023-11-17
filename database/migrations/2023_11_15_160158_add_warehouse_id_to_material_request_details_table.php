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
            $table->bigInteger('warehouse_id')->nullable()->after('place_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material_request_details', function (Blueprint $table) {
            $table->dropColumn('warehouse_id');
        });
    }
};
