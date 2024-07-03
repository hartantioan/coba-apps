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
        Schema::table('production_fg_receive_details', function (Blueprint $table) {
            $table->decimal('total_batch',20,5)->nullable()->after('grade_id');
            $table->decimal('total_material',20,5)->nullable()->after('total_batch');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_fg_receive_details', function (Blueprint $table) {
            $table->dropColumn('total_batch','total_material');
        });
    }
};
