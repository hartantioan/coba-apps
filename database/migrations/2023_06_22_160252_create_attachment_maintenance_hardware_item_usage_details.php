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
        Schema::create('attachment_maintenance_hardware_item_usage_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('maintenance_hardware_item_usage_id')->nullable();
            $table->string('file_name')->nullable();
            $table->string('path')->nullable();
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
        Schema::dropIfExists('attachment_maintenance_hardware_item_usage_details');
    }
};
