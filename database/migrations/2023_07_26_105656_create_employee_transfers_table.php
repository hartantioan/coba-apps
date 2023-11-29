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
        Schema::create('employee_transfers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('account_id')->nullable();
            $table->bigInteger('plant_id')->nullable();
            $table->bigInteger('manager_id')->nullable();
            $table->bigInteger('department_id')->nullable();
            $table->string('position_id')->nullable();
            $table->char('type',1)->nullable();
            $table->string('code',155)->unique();
            $table->string('note')->nullable();
            $table->char('status', 1)->nullable();
            $table->date('post_date')->nullable();
            $table->date('valid_date')->nullable();
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
        Schema::dropIfExists('employee_transfers');
    }
};
