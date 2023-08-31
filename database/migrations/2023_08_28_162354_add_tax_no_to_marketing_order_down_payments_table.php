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
        Schema::table('marketing_order_down_payments', function (Blueprint $table) {
            $table->string('tax_no',155)->nullable()->after('document');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketing_order_down_payments', function (Blueprint $table) {
            $table->dropColumn('tax_no');
        });
    }
};
