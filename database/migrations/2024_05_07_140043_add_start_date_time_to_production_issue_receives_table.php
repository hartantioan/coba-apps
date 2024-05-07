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
        Schema::table('production_issue_receives', function (Blueprint $table) {
            $table->timestamp('start_process_time')->nullable()->after('post_date');
            $table->timestamp('end_process_time')->nullable()->after('start_process_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_issue_receives', function (Blueprint $table) {
            $table->dropColumn('start_process_time','end_process_time');
        });
    }
};
