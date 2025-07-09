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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('code',155)->unique();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('store_customer_id')->nullable();
            $table->date('post_date')->nullable();
            $table->decimal('grandtotal', 20, 5)->nullable();
            $table->decimal('discount', 20, 5)->nullable();
            $table->char('status', 1)->nullable();

            $table->bigInteger('void_id')->nullable();
            $table->text('void_note')->nullable();
            $table->date('void_date')->nullable();

            $table->bigInteger('delete_id')->nullable();
            $table->text('delete_note')->nullable();

            $table->bigInteger('done_id')->nullable();
            $table->date('done_date')->nullable();
            $table->text('done_note')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
