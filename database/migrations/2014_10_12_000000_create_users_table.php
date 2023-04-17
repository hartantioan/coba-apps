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
        if(!Schema::hasTable('users'))
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('photo')->nullable();
            $table->string('signature')->nullable();
            $table->string('name')->nullable();
            $table->string('employee_no',155)->unique();
            $table->string('password')->nullable();
            $table->string('username')->nullable();
            $table->string('phone')->unique();
            $table->text('address')->nullable();
            $table->bigInteger('province_id')->nullable();
            $table->bigInteger('city_id')->nullable();
            $table->string('id_card')->nullable();
            $table->string('id_card_address')->nullable();
            $table->char('type', 2)->nullable();
            $table->bigInteger('group_id')->nullable();
            $table->char('status', 1)->nullable();
            $table->bigInteger('company_id')->nullable();
            $table->bigInteger('place_id')->nullable();
            $table->bigInteger('department_id')->nullable();
            $table->bigInteger('position_id')->nullable();
            $table->string('logo')->nullable();
            $table->string('tax_id', 155)->nullable();
            $table->string('tax_name')->nullable();
            $table->string('tax_address')->nullable();
            $table->string('pic', 155)->nullable();
            $table->string('pic_no', 155)->nullable();
            $table->string('office_no', 155)->nullable();
            $table->string('email', 155)->nullable();
            $table->double('deposit')->nullable();
            $table->double('limit_credit')->nullable();
            $table->integer('top')->nullable();
            $table->integer('top_internal')->nullable();
            $table->double('tolerance_gr')->nullable();
            $table->char('gender',1)->nullable();
            $table->char('married_status',1)->nullable();
            $table->char('married_date')->nullable();
            $table->integer('children',2)->nullable();
            $table->timestamp('last_change_password')->nullable();
            $table->bigInteger('country_id')->nullable();
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
        Schema::dropIfExists('users');
    }
};
