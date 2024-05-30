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
        Schema::table('bom_details', function (Blueprint $table) {
            $table->bigInteger('bom_alternative_id')->nullable()->after('bom_id');
            $table->index(['bom_id','bom_alternative_id','lookable_type','lookable_id'],'bom_detail_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bom_details', function (Blueprint $table) {
            //
        });
    }
};
