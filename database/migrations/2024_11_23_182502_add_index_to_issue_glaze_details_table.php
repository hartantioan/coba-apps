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
        Schema::table('issue_glaze_details', function (Blueprint $table) {
            $table->index(['issue_glaze_id','lookable_type','lookable_id','place_id','warehouse_id'],'isd_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('issue_glaze_details', function (Blueprint $table) {
            //
        });
    }
};
