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
        Schema::create('merge_stocks', function (Blueprint $table) {
            $table->id();
            $table->string('code',155)->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('company_id')->nullable();
            $table->date('post_date')->nullable();
            $table->string('document')->nullable();
            $table->text('note')->nullable();
            $table->decimal('grandtotal', 20, 5)->nullable();
            $table->bigInteger('item_id')->nullable();
            $table->decimal('qty', 20, 5)->nullable();
            $table->bigInteger('to_place_id')->nullable();
            $table->bigInteger('to_warehouse_id')->nullable();
            $table->bigInteger('item_stock_id')->nullable();
            $table->string('batch_no')->nullable();
            $table->char('status',1)->nullable();
            $table->bigInteger('void_id')->nullable();
            $table->string('void_note')->nullable();
            $table->timestamp('void_date')->nullable();
            $table->bigInteger('delete_id')->nullable();
            $table->string('delete_note')->nullable();
            $table->timestamp('delete_date')->nullable();
            $table->bigInteger('done_id')->nullable();
            $table->string('done_note')->nullable();
            $table->timestamp('done_date')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');
            $table->index(['user_id','company_id','item_id','to_place_id','to_warehouse_id','item_stock_id','batch_no'],'merge_stock_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('merge_stocks');
    }
};
