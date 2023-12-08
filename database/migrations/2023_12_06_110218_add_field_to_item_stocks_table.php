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
        Schema::table('item_stocks', function (Blueprint $table) {
            $table->bigInteger('item_shading_id')->nullable()->after('item_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('item_stocks', function (Blueprint $table) {
            $table->dropColumn('item_shading_id');
        });
    }
};
