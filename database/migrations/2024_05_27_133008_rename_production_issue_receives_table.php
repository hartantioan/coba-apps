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
        Schema::rename('production_issue_receives','production_issues');
        Schema::rename('production_issue_receive_details','production_issue_details');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
