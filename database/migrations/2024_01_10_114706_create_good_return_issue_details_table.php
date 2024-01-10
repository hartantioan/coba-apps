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
        Schema::create('good_return_issue_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('good_return_issue_id')->nullable();
            $table->bigInteger('good_issue_detail_id')->nullable();
            $table->bigInteger('item_id')->nullable();
            $table->double('qty')->nullable();
            $table->string('note')->nullable();
            $table->double('total')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['good_return_issue_id','good_issue_detail_id','item_id'],'grid_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('good_return_issue_details');
    }
};
