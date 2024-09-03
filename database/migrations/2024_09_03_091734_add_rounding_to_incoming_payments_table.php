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
            $table->decimal('rounding',20,5)->nullable()->after('wtax');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incoming_payments', function (Blueprint $table) {
            $table->dropColumn('rounding');
        });
    }
};
