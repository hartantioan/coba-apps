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
        Schema::table('incoming_payments', function (Blueprint $table) {
            $table->bigInteger('list_bg_check_id')->nullable()->after('coa_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incoming_payments', function (Blueprint $table) {
            $table->dropColumn('list_bg_check_id');
        });
    }
};
