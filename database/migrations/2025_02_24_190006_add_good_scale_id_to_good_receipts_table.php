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
        Schema::table('good_receipts', function (Blueprint $table) {
            $table->bigInteger('good_scale_id')->nullable()->index()->after('account_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('good_receipts', function (Blueprint $table) {
            $table->dropColumn('good_scale_id');
        });
    }
};
