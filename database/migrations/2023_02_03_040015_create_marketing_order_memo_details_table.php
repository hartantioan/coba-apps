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
        if(!Schema::hasTable('marketing_order_memo_details'))
        Schema::create('marketing_order_memo_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('marketing_order_memo_id')->nullable();
            $table->string('description')->nullable();
            $table->double('nominal')->nullable();
            $table->double('percent_tax')->nullable();
            $table->double('nominal_gross')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('marketing_order_memo_details');
    }
};
