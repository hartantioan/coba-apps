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
        Schema::create('purchase_order_detail_compositions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('pod_id')->nullable();
            $table->bigInteger('pr_id')->nullable();
            $table->double('qty')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['pod_id', 'pr_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_order_detail_compositions');
    }
};
