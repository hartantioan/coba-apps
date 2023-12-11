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
        Schema::table('production_issue_receives', function (Blueprint $table) {
            $table->bigInteger('production_order_id')->nullable()->after('company_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_issue_receives', function (Blueprint $table) {
            $table->dropColumn('production_order_id');
        });
    }
};
