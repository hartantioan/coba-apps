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
        Schema::table('journal_details', function (Blueprint $table) {
            $table->string('lookable_type',155)->nullable()->after('note2')->index();
            $table->bigInteger('lookable_id')->nullable()->after('lookable_type')->index();
            $table->string('detailable_type',155)->nullable()->after('lookable_id')->index();
            $table->bigInteger('detailable_id')->nullable()->after('detailable_type')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('journal_details', function (Blueprint $table) {
            $table->dropColumn('lookable_type','lookable_id','detailable_type','detailable_id');
        });
    }
};
