<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_item_pricelists', function (Blueprint $table) {
            $table->integer('qty_discount')->nullable()->after('discount');
        });
    }

    public function down(): void
    {
        Schema::table('store_item_pricelists', function (Blueprint $table) {
            $table->dropColumn('qty_discount');
        });
    }

};
