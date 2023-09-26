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
        Schema::dropIfExists('production_issues');
        Schema::dropIfExists('production_issue_details');
        Schema::dropIfExists('production_orders');
        Schema::dropIfExists('production_order_details');
        Schema::dropIfExists('production_receipts');
        Schema::dropIfExists('production_receipt_details');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
