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
        Schema::create('request_repair_hardware_items_usages', function (Blueprint $table) {
            $table->id();
            $table->string('code',155)->unique();
            $table->bigInteger('hardware_item_id')->nullable();
            $table->bigInteger('company_id')->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->date('post_date')->nullable();
            $table->string('complaint')->nullable();
            $table->char('status', 1)->nullable();
            $table->bigInteger('void_id')->nullable();
            $table->string('void_note')->nullable();
            $table->timestamp('void_date')->nullable(); 
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
        Schema::dropIfExists('request_repair_hardware_items_usages');
    }
};
