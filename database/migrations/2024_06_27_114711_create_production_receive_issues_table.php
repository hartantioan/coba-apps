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
        Schema::create('production_receive_issues', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('production_receive_id')->nullable();
            $table->biginteger('production_issue_id')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['production_receive_id','production_issue_id'],'pri_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_receive_issues');
    }
};
