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
        Schema::create('good_issue_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('good_issue_id')->nullable();
            $table->bigInteger('item_stock_id')->nullable();
            $table->double('qty')->nullable();
            $table->double('price')->nullable();
            $table->double('total')->nullable();
            $table->string('note')->nullable();
            $table->bigInteger('coa_id')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['good_issue_id','item_stock_id','coa_id'],'good_issue_details_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('good_issue_details');
    }
};
