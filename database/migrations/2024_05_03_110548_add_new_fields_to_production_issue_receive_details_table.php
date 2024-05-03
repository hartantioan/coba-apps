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
        Schema::table('production_issue_receive_details', function (Blueprint $table) {
            $table->decimal('qty_planned',20,5)->nullable()->after('total');
            $table->decimal('nominal_planned',20,5)->nullable()->after('qty_planned');
            $table->decimal('total_planned',20,5)->nullable()->after('nominal_planned');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_issue_receive_details', function (Blueprint $table) {
            $table->dropColumn('qty_planned','nominal_planned','total_planned');
        });
    }
};
