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
        Schema::create('retirement_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('retirement_id')->nullable();
            $table->bigInteger('asset_id')->nullable();
            $table->double('qty')->nullable();
            $table->bigInteger('unit_id')->nullable();
            $table->double('retirement_nominal')->nullable();
            $table->string('note')->nullable();
            $table->bigInteger('coa_id')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['retirement_id', 'asset_id', 'unit_id', 'coa_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('retirement_details');
    }
};
