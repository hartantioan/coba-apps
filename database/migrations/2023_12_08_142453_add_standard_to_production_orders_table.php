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
        Schema::table('production_orders', function (Blueprint $table) {
            $table->double('standard_item_cost')->nullable()->after('status');
            $table->double('standard_resource_cost')->nullable()->after('standard_item_cost');
            $table->double('standard_product_cost')->nullable()->after('standard_resource_cost');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_orders', function (Blueprint $table) {
            $table->dropColumn('standard_item_cost','standard_resource_cost','standard_product_cost');
        });
    }
};
