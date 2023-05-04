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
        if (!Schema::hasTable('work_order_part_details')) {
            Schema::create('work_order_part_details', function (Blueprint $table) {
                $table->id();        
                $table->bigInteger('work_order_id')->nullable();
                $table->bigInteger('part_id')->nullable();
                $table->softDeletes('deleted_at');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('work_order_part_details');
    }
};
