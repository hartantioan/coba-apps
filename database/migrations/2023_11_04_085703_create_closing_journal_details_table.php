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
        Schema::create('closing_journal_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('closing_journal_id')->nullable();
            $table->bigInteger('coa_id')->nullable();
            $table->char('type',1)->nullable();
            $table->double('nominal')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');
            $table->index(['closing_journal_id','coa_id'],'cjd_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('closing_journal_details');
    }
};
