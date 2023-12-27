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
            $table->string('return_tax_no',50)->nullable()->after('post_date');
            $table->date('return_date')->nullable()->after('return_tax_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_memos', function (Blueprint $table) {
            $table->dropColumn('return_tax_no','return_date');
        });
    }
};
