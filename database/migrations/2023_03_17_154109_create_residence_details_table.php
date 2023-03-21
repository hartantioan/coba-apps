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
        Schema::create('residence_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('residence_id')->nullable();
            $table->bigInteger('region_id')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['residence_id', 'region_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('residence_details');
    }
};
