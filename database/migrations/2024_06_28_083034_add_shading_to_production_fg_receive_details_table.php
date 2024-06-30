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
        Schema::table('production_fg_receive_details', function (Blueprint $table) {
            $table->bigInteger('item_id')->nullable()->after('production_fg_receive_id')->index();
            $table->string('shading',155)->nullable()->after('pallet_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_fg_receive_details', function (Blueprint $table) {
            $table->dropColumn('item_id','shading');
        });
    }
};
