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
        Schema::create('closing_journals', function (Blueprint $table) {
            $table->id();
            $table->string('code',155)->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('company_id')->nullable();
            $table->date('post_date')->nullable();
            $table->string('month')->nullable();
            $table->string('document')->nullable();
            $table->string('note')->nullable();
            $table->double('grandtotal')->nullable();
            $table->char('status',1)->nullable();
            $table->bigInteger('void_id')->nullable();
            $table->string('void_note')->nullable();
            $table->timestamp('void_date')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');
            $table->index(['user_id','company_id'],'cj_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('closing_journals');
    }
};
