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
        Schema::create('fund_request_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('fund_request_id')->nullable();
            $table->string('note')->nullable();
            $table->double('qty')->nullable();
            $table->bigInteger('unit_id')->nullable();
            $table->double('price')->nullable();
            $table->double('total')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['fund_request_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fund_request_details');
    }
};
