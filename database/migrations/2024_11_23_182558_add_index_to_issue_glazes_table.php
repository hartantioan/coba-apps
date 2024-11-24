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
        Schema::table('issue_glazes', function (Blueprint $table) {
            $table->index(['user_id','company_id','place_id','line_id','item_stock_id'],'is_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('issue_glazes', function (Blueprint $table) {
            //
        });
    }
};
