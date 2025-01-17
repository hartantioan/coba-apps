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
        Schema::create('complaint_sales', function (Blueprint $table) {
            $table->id();
            $table->string('code',155)->nullable();
            $table->text('document')->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('account_id')->nullable();
            $table->string('lookable_type')->nullable();
            $table->bigInteger('lookable_id')->nullable();
            $table->bigInteger('marketing_order_id_complaint')->nullable();
            $table->text('note')->nullable();
            $table->text('note_complaint')->nullable();
            $table->text('solution')->nullable();
            $table->dateTime('post_date')->nullable();
            $table->dateTime('complaint_date')->nullable();
            $table->char('status', 1)->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaint_sales');
    }
};
