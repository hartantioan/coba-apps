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
        Schema::table('item_cogs', function (Blueprint $table) {
            $table->string('detailable_type')->nullable()->after('lookable_id')->index();
            $table->bigInteger('detailable_id')->nullable()->after('detailable_type')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('item_cogs', function (Blueprint $table) {
            $table->dropColumn('detailable_type','detailable_id');
        });
    }
};
