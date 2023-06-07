<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('journal_details'))
        Schema::create('journal_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('journal_id')->nullable();
            $table->bigInteger('cost_distribution_detail_id')->nullable();
            $table->bigInteger('coa_id')->nullable();
            $table->bigInteger('place_id')->nullable();
            $table->bigInteger('line_id')->nullable();
            $table->bigInteger('machine_id')->nullable();
            $table->bigInteger('account_id')->nullable();
            $table->bigInteger('department_id')->nullable();
            $table->bigInteger('warehouse_id')->nullable();
            $table->bigInteger('item_id')->nullable();
            $table->char('type', 1)->nullable();
            $table->double('nominal')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['journal_id', 'coa_id', 'place_id', 'line_id', 'account_id', 'item_id', 'department_id', 'warehouse_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('journal_details');
    }
};
