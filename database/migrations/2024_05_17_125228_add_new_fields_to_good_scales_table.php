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
        Schema::table('good_scales', function (Blueprint $table) {
            $table->timestamp('time_scale_in')->nullable()->after('image_in');
            $table->timestamp('time_scale_out')->nullable()->after('image_out');
            $table->timestamp('time_scale_qc')->nullable()->after('image_qc');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('good_scales', function (Blueprint $table) {
            $table->dropColumn('time_scale_in','time_scale_out','time_scale_qc');
        });
    }
};
