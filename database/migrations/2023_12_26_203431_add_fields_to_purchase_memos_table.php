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
        Schema::table('purchase_memos', function (Blueprint $table) {
            $table->double('rounding')->nullable()->after('grandtotal');
            $table->double('final')->nullable()->after('rounding');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_memos', function (Blueprint $table) {
            $table->dropColumn('rounding','final');
        });
    }
};
