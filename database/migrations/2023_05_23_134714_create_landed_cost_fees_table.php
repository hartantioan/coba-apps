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
        Schema::create('landed_cost_fees', function (Blueprint $table) {
            $table->id();
            $table->string('code',155)->unique();
            $table->string('name')->nullable();
            $table->bigInteger('coa_id')->nullable();
            $table->char('type',1)->nullable();
            $table->char('status',1)->nullable();
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
        Schema::dropIfExists('landed_cost_fees');
    }
};
