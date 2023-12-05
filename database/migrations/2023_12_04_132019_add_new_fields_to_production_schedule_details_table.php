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

        Schema::table('production_schedule_details', function (Blueprint $table) {
            $table->bigInteger('line_id')->nullable()->index()->after('qty');
            $table->string('group',50)->nullable()->after('line_id');
            $table->bigInteger('warehouse_id')->nullable()->index()->after('group');
            $table->string('note')->nullable()->after('warehouse_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_schedule_details', function (Blueprint $table) {
            $table->dropColumn('line_id','group','warehouse_id','note');
        });
    }
};
