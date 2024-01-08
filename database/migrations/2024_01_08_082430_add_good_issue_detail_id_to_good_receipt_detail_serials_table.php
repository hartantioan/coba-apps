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
        Schema::table('good_receipt_detail_serials', function (Blueprint $table) {
            $table->bigInteger('good_issue_detail_id')->nullable()->after('serial_number')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('good_receipt_detail_serials', function (Blueprint $table) {
            $table->dropColumn('good_issue_detail_id');
        });
    }
};
