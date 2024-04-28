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
            $table->bigInteger('item_unit_id')->nullable()->after('qty')->index();
            $table->decimal('qty_conversion',20,5)->nullable()->after('item_unit_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketing_order_details', function (Blueprint $table) {
            $table->dropColumn('item_unit_id','qty_conversion');
        });
    }
};
