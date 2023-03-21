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
        if(!Schema::hasTable('salary_details'))
        Schema::create('salary_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('salary_id')->nullable();
            $table->bigInteger('allowance_employee_id')->nullable();
            $table->double('total_allowance')->nullable();
            $table->double('total_addition')->nullable();
            $table->double('total_deduction')->nullable();
            $table->double('total_loan')->nullable();
            $table->double('total_receive')->nullable();
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
        Schema::dropIfExists('salary_details');
    }
};
