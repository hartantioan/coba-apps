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
        if(!Schema::hasTable('production_orders'))
        Schema::create('production_orders', function (Blueprint $table) {
            $table->id();
            $table->char('code', 50)->unique();
            $table->bigInteger('user_id')->nullable();
            $table->char('type_production', 1)->nullable();
            $table->char('status_production', 1)->nullable();
            $table->bigInteger('item_id')->nullable();
            $table->double('qty_planned')->nullable();
            $table->bigInteger('warehouse_id')->nullable();
            $table->bigInteger('machine_id')->nullable();
            $table->string('document')->nullable();
            $table->double('priority')->nullable();
            $table->date('order_date')->nullable();
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->date('shift_employee_id')->nullable();
            $table->bigInteger('marketing_order_id')->nullable();
            $table->bigInteger('customer_id')->nullable();
            $table->text('note')->nullable();
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
        Schema::dropIfExists('production_orders');
    }
};
