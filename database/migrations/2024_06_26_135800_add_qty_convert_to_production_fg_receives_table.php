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
            $table->decimal('conversion',20,5)->nullable()->after('qty');
            $table->decimal('qty_pallet',20,5)->nullable()->after('conversion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_fg_receives', function (Blueprint $table) {
            $table->dropColumn('conversion','qty_pallet');
        });
    }
};
