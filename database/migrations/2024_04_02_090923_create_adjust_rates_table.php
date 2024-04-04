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
        Schema::create('adjust_rates', function (Blueprint $table) {
            $table->id();
            $table->string('code',155)->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('company_id')->nullable();
            $table->date('post_date')->nullable();
            $table->bigInteger('currency_id')->nullable();
            $table->decimal('currency_rate',20,5)->nullable();
            $table->string('note')->nullable();
            $table->char('status',1)->nullable();
            $table->bigInteger('void_id')->nullable();
            $table->string('void_note')->nullable();
            $table->timestamp('void_date')->nullable();
            $table->bigInteger('delete_id')->nullable();
            $table->string('delete_note')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');
            $table->index(['user_id','company_id','currency_id'],'ar_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adjust_rates');
    }
};
