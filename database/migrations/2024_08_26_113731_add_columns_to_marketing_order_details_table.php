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
        Schema::table('marketing_order_details', function (Blueprint $table) {
            $table->decimal('price_list',20,5)->nullable()->after('qty_uom');
            $table->decimal('price_delivery',20,5)->nullable()->after('price_list');
            $table->decimal('price_type_bp',20,5)->nullable()->after('price_delivery');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketing_order_details', function (Blueprint $table) {
            $table->dropColumn('price_list','price_delivery','price_type_bp');
        });
    }
};
