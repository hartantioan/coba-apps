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
        if(!Schema::hasTable('assets'))
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('code',155)->unique();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('place_id')->nullable();
            $table->bigInteger('department_id')->nullable();
            $table->bigInteger('item_id')->nullable();
            $table->string('name',155)->nullable();
            $table->date('date_start')->nullable();
            $table->date('date_end')->nullable();
            $table->double('nominal')->nullable();
            $table->char('method',1)->nullable();
            $table->bigInteger('cost_coa_id')->nullable();
            $table->string('note')->nullable;
            $table->char('status',1)->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['user_id', 'branch_id', 'plant_id', 'department_id', 'item_id', 'cost_coa_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('assets');
    }
};
