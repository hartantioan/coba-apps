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
        Schema::table('marketing_order_returns', function (Blueprint $table) {
            $table->dropColumn('marketing_order_id','branch_id','date_return','warehouse_id','type_journal','subtotal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketing_order_returns', function (Blueprint $table) {
            //
        });
    }
};
