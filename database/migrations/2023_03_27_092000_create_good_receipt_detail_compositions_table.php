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
        Schema::create('good_receipt_detail_compositions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('grd_id')->nullable();
            $table->bigInteger('po_id')->nullable();
            $table->double('qty')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['grd_id', 'po_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('good_receipt_detail_compositions');
    }
};
