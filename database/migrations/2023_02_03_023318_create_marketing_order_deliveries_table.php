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
        if(!Schema::hasTable('marketing_order_deliveries'))
        Schema::create('marketing_order_deliveries', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('branch_id')->nullable();
            $table->bigInteger('marketing_order_id')->nullable();
            $table->bigInteger('warehouse_id')->nullable();
            $table->string('driver_name')->nullable();
            $table->string('driver_phone')->nullable();
            $table->string('receiver_name')->nullable();
            $table->string('receiver_phone')->nullable();
            $table->date('delivery_date')->nullable();
            $table->date('received_date')->nullable();
            $table->date('due_date')->nullable();
            $table->date('due_date_tt')->nullable();
            $table->string('document_received')->nullable();
            $table->string('document_tt')->nullable();
            $table->string('note')->nullable();
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
        Schema::dropIfExists('marketing_order_deliveries');
    }
};
