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
            $table->bigInteger('item_unit_id')->nullable()->after('stock')->index();
            $table->double('qty_conversion')->nullable()->after('item_unit_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material_request_details', function (Blueprint $table) {
            $table->dropColumn('item_unit_id','qty_conversion');
        });
    }
};
