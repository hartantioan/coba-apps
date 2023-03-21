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
        if(!Schema::hasTable('allowance_employees'))
        Schema::create('allowance_employees', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('branch_id')->nullable();
            $table->bigInteger('plant_id')->nullable();
            $table->bigInteger('department-id')->nullable();
            $table->bigInteger('employee_id')->nullable();
            $table->char('type_payment')->nullable();
            $table->string('start_month', 7)->nullable();
            $table->string('end_month', 7)->nullable();
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
        Schema::dropIfExists('allowance_employees');
    }
};
