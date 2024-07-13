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
        Schema::table('production_issues', function (Blueprint $table) {
            $table->bigInteger('production_receive_id')->nullable()->after('production_fg_receive_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_issues', function (Blueprint $table) {
            $table->dropColumn('production_receive_id');
        });
    }
};
