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
            $table->bigInteger('place_id')->nullable()->after('production_order_id')->index();
            $table->bigInteger('shift_id')->nullable()->after('place_id')->index();
            $table->bigInteger('line_id')->nullable()->after('shift_id')->index();
            $table->bigInteger('machine_id')->nullable()->after('line_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_issue_receives', function (Blueprint $table) {
            //
        });
    }
};
