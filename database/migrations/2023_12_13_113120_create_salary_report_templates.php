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
        Schema::create('salary_report_templates', function (Blueprint $table) {
            $table->id();
            
            $table->bigInteger('salary_report_id')->nullable();
            $table->string('lookable_type',155)->nullable();
            $table->bigInteger('lookable_id')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');
            $table->index(['salary_report_id','lookable_id'],'mrd_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_report_templates');
    }
};
