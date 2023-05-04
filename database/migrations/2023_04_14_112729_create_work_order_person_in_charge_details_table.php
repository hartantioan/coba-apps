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
        if (!Schema::hasTable('work_order_person_in_charge_details')) {
            Schema::create('work_order_person_in_charge_details', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('work_order_id')->nullable();
                $table->bigInteger('user_id')->nullable();
                $table->bigInteger('pic_id')->nullable();
                $table->char('status', 1)->nullable();
                $table->timestamps();
                $table->softDeletes('deleted_at');
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
        Schema::dropIfExists('maintenance_person_in_charge_details');
    }
};
