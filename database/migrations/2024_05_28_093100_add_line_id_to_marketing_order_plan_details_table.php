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
        Schema::table('marketing_order_plan_details', function (Blueprint $table) {
            $table->dropColumn('priority');
            $table->text('note2')->nullable()->after('note');
            $table->bigInteger('line_id')->nullable()->after('note2')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketing_order_plan_details', function (Blueprint $table) {
            //
        });
    }
};
