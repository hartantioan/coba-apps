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
            $table->decimal('conversion',20,5)->nullable()->after('qty');
            $table->decimal('qty_used',20,5)->nullable()->after('conversion');
            $table->decimal('qty_balance',20,5)->nullable()->after('qty_used');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_fg_receive_details', function (Blueprint $table) {
            //
        });
    }
};
