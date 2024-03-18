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
        Schema::table('purchase_invoices', function (Blueprint $table) {
            $table->bigInteger('currency_id')->nullable()->after('type')->index();
            $table->decimal('currency_rate',20,5)->nullable()->after('currency_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_invoices', function (Blueprint $table) {
            $table->dropColumn('currency_id','currency_rate');
        });
    }
};
