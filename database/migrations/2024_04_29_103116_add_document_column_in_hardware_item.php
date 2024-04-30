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
        Schema::table('hardware_items', function (Blueprint $table) {
            $table->dropColumn('location');
            $table->dropColumn('ip_address');
            $table->dropColumn('info');
            $table->dropColumn('nominal');
            $table->dropColumn('currency_id');
            $table->string('detail1')->nullable();
            $table->string('detail2')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hardware_items', function (Blueprint $table) {
            //
        });
    }
};
