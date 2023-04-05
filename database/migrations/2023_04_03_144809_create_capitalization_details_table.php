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
        Schema::create('capitalization_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('capitalization_id')->nullable();
            $table->bigInteger('asset_id')->nullable();
            $table->double('qty')->nullable();
            $table->bigInteger('unit_id')->nullable();
            $table->double('price')->nullable();
            $table->double('total')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['capitalization_id', 'asset_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('capitalization_details');
    }
};
