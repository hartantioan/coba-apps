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
        if(!Schema::hasTable('marketing_orders'))
        Schema::create('marketing_orders', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('branch_id')->nullable();
            $table->bigInteger('plant_id')->nullable();
            $table->bigInteger('warehouse_id')->nullable();
            $table->char('type_sales', 1)->nullable();
            $table->string('special_note')->nullable();
            $table->date('valid_until')->nullable();
            $table->bigInteger('customer_id')->nullable();
            $table->string('document')->nullable();
            $table->char('type_delivery', 1)->nullable();
            $table->bigInteger('sender_id')->nullable();
            $table->date('delivery_date')->nullable();
            $table->char('type_dependent',1)->nullable();
            $table->date('create_date')->nullable();
            $table->date('percent_tax')->nullable();
            $table->char('payment_type', 1)->nullable();
            $table->integer('top_internal')->nullable();
            $table->integer('top_customer')->nullable();
            $table->char('is_guarantee', 1)->nullable();
            $table->string('shipment_address')->nullable();
            $table->string('billing_address')->nullable();
            $table->string('destination_address')->nullable();
            $table->bigInteger('province_id')->nullable();
            $table->bigInteger('city_id')->nullable();
            $table->bigInteger('district_id')->nullable();
            $table->bigInteger('urban_id')->nullable();
            $table->bigInteger('sales_id')->nullable();
            $table->double('subtotal')->nullable();
            $table->double('tax')->nullable();
            $table->double('grandtotal')->nullable();
            $table->char('status',1)->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('marketing_orders');
    }
};
