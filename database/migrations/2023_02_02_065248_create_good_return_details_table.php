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
        if(!Schema::hasTable('good_return_details'))
        Schema::create('good_return_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('good_return_id')->nullable();
            $table->bigInteger('good_receipt_detail_id')->nullable();
            $table->bigInteger('item_id')->nullable();
            $table->double('qty')->nullable();
            $table->string('note')->nullable();
            $table->string('note2')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['good_return_id', 'good_receipt_detail_id', 'item_id'],'good_returns_details_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('good_return_details');
    }
};
