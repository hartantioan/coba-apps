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
        Schema::create('hardware_item_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('hardware_item_id')->nullable();
            $table->string('specification')->nullable();
            $table->string('info')->nullable();
            $table->char('status', 1)->nullable();
            $table->softDeletes('deleted_at'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hardware_item_details');
    }
};
