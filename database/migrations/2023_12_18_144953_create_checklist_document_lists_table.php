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
        Schema::create('checklist_document_lists', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('checklist_document_id')->nullable()->index();
            $table->string('lookable_type',155)->nullable()->index();
            $table->bigInteger('lookable_id')->nullable()->index();
            $table->char('value',1)->nullable();
            $table->string('note')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checklist_document_lists');
    }
};
