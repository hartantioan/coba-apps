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
        Schema::create('hardware_item_groups', function (Blueprint $table) {
            $table->id();
            $table->string('code',155)->unique();
            $table->string('name')->nullable();
            $table->bigInteger('department_id')->nullable();
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
        Schema::dropIfExists('hardware_items_groups');
    }
};
