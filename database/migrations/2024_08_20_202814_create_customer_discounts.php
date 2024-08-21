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
        Schema::create('customer_discounts', function (Blueprint $table) {
            $table->id();
            $table->string('code',155)->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('account_id')->nullable();
            $table->bigInteger('city_id')->nullable();
            $table->bigInteger('brand_id')->nullable();
            $table->bigInteger('type_id')->nullable();
            $table->char('payment_type',1)->nullable();
            $table->decimal('disc1',20,5)->nullable();
            $table->decimal('disc2',20,5)->nullable();
            $table->decimal('disc3',20,5)->nullable();
            $table->date('post_date')->nullable();
            $table->char('status',1)->nullable();
         
            $table->timestamps();
            $table->softDeletes('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_discounts');
    }
};
