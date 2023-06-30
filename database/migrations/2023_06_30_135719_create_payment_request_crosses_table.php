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
        Schema::create('payment_request_crosses', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('payment_request_id')->nullable();
            $table->string('lookable_type',155)->nullable();
            $table->bigInteger('lookable_id')->nullable();
            $table->double('nominal')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['payment_request_id','lookable_id'],'payment_request_crosses_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_request_crosses');
    }
};
