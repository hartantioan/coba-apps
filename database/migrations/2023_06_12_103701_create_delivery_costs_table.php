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
        Schema::create('delivery_costs', function (Blueprint $table) {
            $table->id();
            $table->string('code',155)->unique();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('account_id')->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->bigInteger('from_city_id')->nullable();
            $table->bigInteger('from_subdistrict_id')->nullable();
            $table->bigInteger('to_city_id')->nullable();
            $table->bigInteger('to_subdistrict_id')->nullable();
            $table->double('tonnage')->nullable();
            $table->double('nominal')->nullable();
            $table->char('status', 1)->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');
            
            $table->index(['user_id','account_id','from_city_id','from_subdistrict_id','to_city_id','to_subdistrict_id'],'delivery_cost_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('delivery_costs');
    }
};
