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
        Schema::create('revision_attendance_hrd', function (Blueprint $table) {
            $table->id();
            $table->string('code',155)->unique();
            $table->bigInteger('user_id')->nullable();
            $table->date('post_date')->nullable();
            $table->bigInteger('period_id')->nullable();
            $table->bigInteger('company_id')->nullable();
            $table->char('status', 1)->nullable();
            $table->char('void_id',1)->nullable();
            $table->date('void_date')->nullable();
            $table->string('void_note')->nullable();
            $table->bigInteger('delete_id')->nullable();
            $table->string('delete_note')->nullable();
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
        Schema::dropIfExists('revision_attendance_hrd');
    }
};
