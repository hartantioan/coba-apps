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
        Schema::table('good_issue_details', function (Blueprint $table) {
            $table->string('lookable_type',155)->nullable()->after('coa_id');
            $table->bigInteger('lookable_id')->nullable()->after('lookable_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('good_issue_details', function (Blueprint $table) {
            $table->dropColumn('lookable_type','lookable_id');
        });
    }
};
