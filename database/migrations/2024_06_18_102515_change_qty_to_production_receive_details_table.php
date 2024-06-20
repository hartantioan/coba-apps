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
        Schema::table('production_receive_details', function (Blueprint $table) {
            $table->decimal('qty_planned',20,5)->nullable()->after('qty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_receive_details', function (Blueprint $table) {
            $table->dropColumn('qty_planned');
        });
    }
};
