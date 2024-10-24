<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('item_pricelists', function (Blueprint $table) {
            $table->dropColumn(['customer_id', 'brand_id', 'start_date', 'end_date']);
            $table->decimal('discount', 20, 5)->nullable();
            $table->decimal('sell_price', 20, 5)->nullable();
        });
    }

    public function down()
    {

    }
};
