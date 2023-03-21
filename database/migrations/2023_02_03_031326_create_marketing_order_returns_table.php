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
        if(!Schema::hasTable('marketing_order_returns'))
        Schema::create('marketing_order_returns', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('marketing_order_id')->nullable();
            $table->bigInteger('branch_id')->nullable();
            $table->date('date_return')->nullable();
            $table->bigInteger('warehouse_id')->nullable();
            $table->string('document')->nullable();
            $table->string('note')->nullable();
            $table->char('type_journal', 1)->nullable();
            $table->double('subtotal')->nullable();
            $table->double('tax')->nullable();
            $table->double('grandtotal')->nullable();
            $table->char('status')->nullable();
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
        Schema::dropIfExists('marketing_order_returns');
    }
};
