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
        Schema::table('issue_glazes', function (Blueprint $table) {
            $table->bigInteger('item_id')->nullable()->index()->after('line_id');
            $table->bigInteger('item_stock_id')->nullable()->index()->after('item_id');
            $table->decimal('qty',20,5)->nullable()->after('item_stock_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('issue_glazes', function (Blueprint $table) {
            $table->dropColumn('item_id','item_stock_id','qty');
        });
    }
};
