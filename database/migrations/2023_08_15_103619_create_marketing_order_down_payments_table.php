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
        Schema::create('marketing_order_down_payments', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('account_id')->nullable();
            $table->bigInteger('company_id')->nullable();
            $table->bigInteger('tax_id')->nullable();
            $table->char('is_tax', 1)->nullable();
            $table->char('is_include_tax', 1)->nullable();
            $table->double('percent_tax')->nullable();
            $table->date('post_date')->nullable();
            $table->date('due_date')->nullable();
            $table->char('status', 1)->nullable();
            $table->char('type', 1)->nullable();
            $table->bigInteger('currency_id')->nullable();
            $table->double('currency_rate')->nullable();
            $table->double('subtotal')->nullable();
            $table->double('discount')->nullable();
            $table->double('total')->nullable();
            $table->double('tax')->nullable();
            $table->double('grandtotal')->nullable();
            $table->string('document')->nullable();
            $table->text('note')->nullable();
            $table->bigInteger('void_id')->nullable();
            $table->string('void_note')->nullable();
            $table->timestamp('void_date')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['user_id', 'account_id', 'company_id', 'tax_id', 'currency_id'],'marketing_dp_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketing_order_down_payments');
    }
};
