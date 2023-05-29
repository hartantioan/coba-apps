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
        if(!Schema::hasTable('purchase_request_details'))
        Schema::create('purchase_request_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('purchase_request_id')->nullable();
            $table->bigInteger('item_id')->nullable();
            $table->double('qty')->nullable();
            $table->string('note')->nullable();
            $table->date('required_date')->nullable();
            $table->bigInteger('place_id')->nullable();
            $table->bigInteger('line_id')->nullable();
            $table->bigInteger('department_id')->nullable();
            $table->bigInteger('warehouse_id')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['purchase_request_id', 'item_id', 'place_id', 'department_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_request_details');
    }
};
