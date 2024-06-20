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
        Schema::create('cancel_documents', function (Blueprint $table) {
            $table->id();
            $table->string('code',155)->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->date('post_date')->nullable();
            $table->string('lookable_type',155)->nullable()->index();
            $table->bigInteger('lookable_id')->nullable()->index();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['user_id','lookable_type','lookable_id'],'cd_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cancel_documents');
    }
};
