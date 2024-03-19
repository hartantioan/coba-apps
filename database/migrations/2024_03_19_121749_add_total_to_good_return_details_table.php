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
        Schema::table('good_return_details', function (Blueprint $table) {
            $table->decimal('total',20,5)->nullable()->after('note2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('good_return_details', function (Blueprint $table) {
            $table->dropColumn('total');
        });
    }
};
