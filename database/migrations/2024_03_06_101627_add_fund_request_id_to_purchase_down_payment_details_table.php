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
        Schema::table('purchase_down_payment_details', function (Blueprint $table) {
            $table->bigInteger('fund_request_id')->nullable()->after('purchase_order_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_down_payment_details', function (Blueprint $table) {
            $table->dropColumn('fund_request_id');
        });
    }
};
