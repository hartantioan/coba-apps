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
        Schema::table('purchase_down_payments', function (Blueprint $table) {
            $table->bigInteger('wtax_id')->nullable()->after('percent_tax')->index();
            $table->double('percent_wtax')->nullable()->after('wtax_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_down_payments', function (Blueprint $table) {
            $table->dropColumn('wtax_id','percent_wtax');
        });
    }
};
