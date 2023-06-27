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
        Schema::table('good_receipt_details', function (Blueprint $table) {
            $table->bigInteger('good_scale_detail_id')->after('purchase_order_detail_id')->nullable();

            $table->index(['good_scale_detail_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('good_receipt_details', function (Blueprint $table) {
            $table->dropColumn('good_scale_detail_id');
        });
    }
};
