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
        Schema::create('landed_cost_fee_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('landed_cost_id')->nullable();
            $table->bigInteger('landed_cost_fee_id')->nullable();
            $table->double('total')->nullable();
            $table->char('is_include_tax',1)->nullable();
            $table->double('percent_tax')->nullable();
            $table->double('percent_wtax')->nullable();
            $table->double('tax')->nullable();
            $table->double('wtax')->nullable();
            $table->double('grandtotal')->nullable();
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
        Schema::dropIfExists('landed_cost_fee_details');
    }
};
