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
            $table->decimal('standard_item_cost', 20, 5)->change();
            $table->decimal('standard_resource_cost', 20, 5)->change();
            $table->decimal('actual_item_cost', 20, 5)->change();
            $table->decimal('actual_resource_cost', 20, 5)->change();
            $table->decimal('total_product_cost', 20, 5)->change();
            $table->decimal('planned_qty', 20, 5)->change();
            $table->decimal('completed_qty', 20, 5)->change();
            $table->decimal('rejected_qty', 20, 5)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_orders', function (Blueprint $table) {
            //
        });
    }
};
