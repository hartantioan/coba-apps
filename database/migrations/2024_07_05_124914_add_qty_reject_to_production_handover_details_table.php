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
        Schema::table('production_handover_details', function (Blueprint $table) {
            $table->decimal('qty_received',20,5)->nullable()->after('qty_reject');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_handover_details', function (Blueprint $table) {
            $table->dropColumn('qty_received');
        });
    }
};
