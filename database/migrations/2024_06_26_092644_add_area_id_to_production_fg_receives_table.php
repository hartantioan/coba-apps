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
        Schema::table('production_fg_receives', function (Blueprint $table) {
            $table->bigInteger('area_id')->nullable()->after('qty')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_fg_receives', function (Blueprint $table) {
            $table->dropColumn('area_id');
        });
    }
};
