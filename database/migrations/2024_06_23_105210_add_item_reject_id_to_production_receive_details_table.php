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
        Schema::table('production_receive_details', function (Blueprint $table) {
            $table->bigInteger('item_reject_id')->nullable()->after('bom_id')->index();
            $table->decimal('qty_reject',20,5)->nullable()->after('qty_planned');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_receive_details', function (Blueprint $table) {
            $table->dropColumn('item_reject_id','qty_reject');
        });
    }
};
