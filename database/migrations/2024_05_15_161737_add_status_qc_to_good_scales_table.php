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
            $table->char('status_qc',1)->nullable()->after('status');
            $table->text('note_qc')->nullable()->after('status_qc');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('good_scales', function (Blueprint $table) {
            $table->dropColumn('status_qc','note_qc');
        });
    }
};
