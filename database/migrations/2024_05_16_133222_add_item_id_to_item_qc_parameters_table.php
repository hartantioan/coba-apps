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
        Schema::table('item_qc_parameters', function (Blueprint $table) {
            $table->bigInteger('item_id')->nullable()->after('is_affect_qty')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('item_qc_parameters', function (Blueprint $table) {
            $table->dropColumn('item_id');
        });
    }
};
