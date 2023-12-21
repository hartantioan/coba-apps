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
        Schema::create('salary_report_users', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('salary_report_id')->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->double('total_plus')->nullable();
            $table->double('total_minus')->nullable();
            $table->double('total_received')->nullable();
            $table->softDeletes('deleted_at');
            $table->timestamps();
            $table->index(['salary_report_id','user_id'],'mrd_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_report_users');
    }
};
