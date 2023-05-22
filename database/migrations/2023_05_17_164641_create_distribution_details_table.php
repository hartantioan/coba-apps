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
        Schema::create('distribution_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('distribution_id')->nullable();
            $table->string('lookable_type')->nullable();
            $table->bigInteger('lookable_id')->nullable();
            $table->double('percentage')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['distribution_id','lookable_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('distribution_details');
    }
};
