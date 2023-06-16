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
        Schema::create('incoming_payment_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('incoming_payment_id')->nullable();
            $table->string('lookable_type')->nullable();
            $table->bigInteger('lookable_id')->nullable();
            $table->bigInteger('cost_distribution_id')->nullable();
            $table->double('total')->nullable();
            $table->double('rounding')->nullable();
            $table->double('subtotal')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['incoming_payment_id','lookable_id','cost_distribution_id'],'incoming_detail_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('incoming_payment_details');
    }
};
