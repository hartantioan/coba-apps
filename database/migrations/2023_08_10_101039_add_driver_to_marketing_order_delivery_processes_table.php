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
        Schema::table('marketing_order_delivery_processes', function (Blueprint $table) {
            $table->bigInteger('user_driver_id')->nullable()->after('post_date');
            $table->string('driver_name',155)->nullable()->after('user_driver_id');
            $table->string('driver_hp',50)->nullable()->after('driver_name');
            $table->string('vehicle_name',155)->nullable()->after('driver_hp');
            $table->string('vehicle_no',155)->nullable()->after('vehicle_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('marketing_order_delivery_processes', function (Blueprint $table) {
            $table->dropColumn('user_driver_id','driver_name','driver_hp');
        });
    }
};
