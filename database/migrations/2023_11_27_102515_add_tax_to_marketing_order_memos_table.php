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
        Schema::table('marketing_order_memos', function (Blueprint $table) {
            $table->char('is_include_tax',1)->after('tax_no')->nullable();
            $table->double('percent_tax')->after('is_include_tax')->nullable();
            $table->bigInteger('tax_id')->after('percent_tax')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketing_order_memos', function (Blueprint $table) {
            $table->dropColumn('is_include_tax','percent_tax','tax_id');
        });
    }
};
