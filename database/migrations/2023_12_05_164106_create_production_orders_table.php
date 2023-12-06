<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('production_orders', function (Blueprint $table) {
            $table->id();
            $table->string('code',155)->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('company_id')->nullable();
            $table->bigInteger('production_schedule_id')->nullable();
            $table->bigInteger('production_schedule_detail_id')->nullable();
            $table->bigInteger('warehouse_id')->nullable();
            $table->date('post_date')->nullable();
            $table->string('note')->nullable();
            $table->char('status',1)->nullable();
            $table->double('actual_item_cost')->nullable();
            $table->double('actual_resource_cost')->nullable();
            $table->double('total_product_cost')->nullable();
            $table->double('planned_qty')->nullable();
            $table->double('completed_qty')->nullable();
            $table->double('rejected_qty')->nullable();
            $table->bigInteger('void_id')->nullable();
            $table->string('void_note')->nullable();
            $table->timestamp('void_date')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');
            $table->index(['user_id','company_id','production_schedule_id','production_schedule_detail_id','warehouse_id'],'pord_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_orders');
    }
};
