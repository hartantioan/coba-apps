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
        Schema::table('production_repack_details', function (Blueprint $table) {
            $table->bigInteger('line_id')->nullable()->after('area_id')->index();
            $table->bigInteger('shift_id')->nullable()->after('line_id')->index();
            $table->char('group',5)->nullable()->after('shift_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_repack_details', function (Blueprint $table) {
            $table->dropColumn('line_id','shift_id','group');
        });
    }
};
