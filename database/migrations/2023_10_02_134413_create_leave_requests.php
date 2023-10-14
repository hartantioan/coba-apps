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
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('company_id')->nullable();
            $table->bigInteger('account_id')->nullable();
            $table->bigInteger('leave_type_id')->nullable();
            $table->char('start_time',10)->nullable();
            $table->char('end_time',10)->nullable();
            $table->date('end_date')->nullable();
            $table->date('start_date')->nullable();
            $table->date('post_date')->nullable();
            $table->string('note')->nullable();
            $table->string('document')->nullable();
            $table->char('void_id',1)->nullable();
            $table->date('void_date')->nullable();
            $table->string('void_note')->nullable();
            $table->char('status',1)->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
