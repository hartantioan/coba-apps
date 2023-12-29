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
        Schema::table('inventory_transfer_outs', function (Blueprint $table) {
            $table->bigInteger('delete_id')->nullable()->after('void_date');
            $table->string('delete_note')->nullable()->after('delete_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_transfer_outs', function (Blueprint $table) {
            $table->dropColumn('delete_id','delete_note');
        });
    }
};
