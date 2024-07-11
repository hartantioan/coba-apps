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
        Schema::table('adjust_rates', function (Blueprint $table) {
            $table->date('reverse_date')->nullable()->after('post_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adjust_rates', function (Blueprint $table) {
            $table->dropColumn('reverse_date');
        });
    }
};
