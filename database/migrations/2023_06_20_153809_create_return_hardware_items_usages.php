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
        Schema::create('return_hardware_items_usages', function (Blueprint $table) {
            $table->id();
            $table->string('code',155)->unique();
            $table->bigInteger('reception_hardware_item_usage_id')->nullable();
            $table->date('date')->nullable();
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
        Schema::dropIfExists('return_hardware_items_usages');
    }
};
