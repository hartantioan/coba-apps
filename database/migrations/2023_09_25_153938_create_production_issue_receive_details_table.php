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
        Schema::create('production_issue_receive_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('production_issue_receive_id')->nullable();
            $table->bigInteger('production_schedule_detail_id')->nullable();
            $table->bigInteger('production_issue_receive_detail_id')->nullable();
            $table->string('lookable_type',155)->nullable();
            $table->bigInteger('lookable_id')->nullable();
            $table->bigInteger('qty')->nullable();
            $table->char('type',1)->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['production_issue_receive_id','production_schedule_detail_id','production_issue_receive_detail_id','lookable_id'],'pir_detail_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_issue_receive_details');
    }
};
