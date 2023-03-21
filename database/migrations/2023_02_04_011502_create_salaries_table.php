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
        if(!Schema::hasTable('salaries'))
        Schema::create('salaries', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('branch_id')->nullable();
            $table->bigInteger('plant_id')->nullable();
            $table->bigInteger('department_id')->nullable();
            $table->string('month', 7)->nullable();
            $table->date('date_posting')->nullable();
            $table->date('date_start')->nullable();
            $table->date('date_end')->nullable();
            $table->double('total_allowance')->nullable();
            $table->double('total_addition')->nullable();
            $table->double('total_deduction')->nullable();
            $table->double('total_load')->nullable();
            $table->double('total_receive')->nullable();
            $table->text('note')->nullable();
            $table->char('status', 1)->nullable();
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
        Schema::dropIfExists('salaries');
    }
};
