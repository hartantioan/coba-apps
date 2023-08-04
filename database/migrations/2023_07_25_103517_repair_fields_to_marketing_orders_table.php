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
        Schema::table('marketing_orders', function (Blueprint $table) {
            $table->dropColumn('branch_id','plant_id','warehouse_id','special_note','valid_until','customer_id','type_dependent','create_date','urban_id','percent_tax','district_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('marketing_orders', function (Blueprint $table) {
            $table->bigInteger('branch_id')->nullable();
            $table->bigInteger('plant_id')->nullable();
            $table->bigInteger('warehouse_id')->nullable();
            $table->string('special_note')->nullable();
            $table->date('valid_until')->nullable();
            $table->bigInteger('customer_id')->nullable();
            $table->char('type_dependent',1)->nullable();
            $table->date('create_date')->nullable();
            $table->bigInteger('urban_id')->nullable();
            $table->bigInteger('district_id')->nullable();
        });
    }
};
