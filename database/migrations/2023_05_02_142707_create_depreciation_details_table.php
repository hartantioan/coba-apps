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
        Schema::create('depreciation_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('depreciation_id')->nullable();
            $table->bigInteger('asset_id')->nullable();
            $table->double('nominal')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['depreciation_id','asset_id'],'depreciation_details_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('depreciation_details');
    }
};
