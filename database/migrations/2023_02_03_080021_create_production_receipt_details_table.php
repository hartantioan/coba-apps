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
        if(!Schema::hasTable('production_receipt_details'))
        Schema::create('production_receipt_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('production_receipt_id')->nullable();
            $table->bigInteger('production_order_id')->nullable();
            $table->double('qty')->nullable();
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
        Schema::dropIfExists('production_receipt_details');
    }
};
