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
            $table->string('group',20)->nullable()->after('qty');
            $table->bigInteger('pallet_id')->nullable()->after('group')->index();
            $table->bigInteger('grade_id')->nullable()->after('pallet_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_fg_receive_details', function (Blueprint $table) {
            $table->dropColumn('group','pallet_id','grade_id');
        });
    }
};
