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
        Schema::table('production_fg_receives', function (Blueprint $table) {
            $table->bigInteger('item_id')->nullable()->after('production_order_id')->index();
            $table->text('note')->nullable()->after('document');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_fg_receives', function (Blueprint $table) {
            $table->dropColumn('item_id','note');
        });
    }
};
