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
        Schema::table('production_orders', function (Blueprint $table) {
            $table->decimal('total_production_time',20,5)->nullable()->after('rejected_qty');
            $table->decimal('total_additional_time',20,5)->nullable()->after('total_production_time');
            $table->decimal('total_run_time',20,5)->nullable()->after('total_additional_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_orders', function (Blueprint $table) {
            //
        });
    }
};
