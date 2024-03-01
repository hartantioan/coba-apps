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
        Schema::create('good_issue_request_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('good_issue_request_id')->nullable();
            $table->bigInteger('item_id')->nullable();
            $table->decimal('qty',20,5)->nullable();
            $table->decimal('stock',20,5)->nullable();
            $table->bigInteger('item_unit_id')->nullable()->index();
            $table->decimal('qty_conversion',20,5)->nullable();
            $table->string('note')->nullable();
            $table->string('note2')->nullable();
            $table->date('required_date')->nullable();
            $table->bigInteger('place_id')->nullable();
            $table->bigInteger('line_id')->nullable()->index();
            $table->bigInteger('machine_id')->nullable()->index();
            $table->bigInteger('department_id')->nullable()->index();
            $table->bigInteger('warehouse_id')->nullable()->index();
            $table->bigInteger('project_id')->nullable()->index();
            $table->char('status',1)->nullable();
            $table->string('requester',155)->nullable();
            $table->decimal('total',20,5)->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['good_issue_request_id', 'item_id', 'place_id'],'gird_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('good_issue_request_details');
    }
};
