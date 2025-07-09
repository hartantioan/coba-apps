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
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->bigInteger('user_id')->nullable();
            $table->string('note')->nullable();
            $table->char('type_sales', 1)->nullable();
            $table->bigInteger('customer_id')->nullable();
            $table->string('document')->nullable();
            $table->date('post_date')->nullable();
            $table->char('payment_type', 1)->nullable();
            $table->double('subtotal')->nullable();
            $table->double('tax')->nullable();
            $table->double('grandtotal')->nullable();
            $table->char('status',1)->nullable();
            $table->bigInteger('void_id')->nullable();
            $table->string('void_note')->nullable();
            $table->timestamp('void_date')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};
