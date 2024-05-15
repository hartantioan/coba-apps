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
        Schema::table('purchase_down_payments', function (Blueprint $table) {
            //
            $table->text('note')->nullable()->change();
            $table->text('note_external')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_down_payments', function (Blueprint $table) {
            //
        });
    }
};
