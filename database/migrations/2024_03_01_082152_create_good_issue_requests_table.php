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
        Schema::create('good_issue_requests', function (Blueprint $table) {
            $table->id();
            $table->string('code',155)->unique();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('company_id')->nullable();
            $table->char('status', 1)->nullable();
            $table->decimal('grandtotal',20,5)->nullable();
            $table->date('post_date')->nullable();
            $table->text('note')->nullable();
            $table->bigInteger('void_id')->nullable();
            $table->string('void_note')->nullable();
            $table->timestamp('void_date')->nullable();
            $table->bigInteger('delete_id')->nullable();
            $table->string('delete_note')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['user_id', 'company_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('good_issue_requests');
    }
};
