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
        Schema::create('handover_purchase_invoices', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->nullable()->index();
            $table->string('code',155)->unique();
            $table->bigInteger('place_id')->nullable()->index();
            $table->bigInteger('company_id')->nullable()->index();
            $table->bigInteger('account_id')->nullable()->index();
            $table->date('post_date')->nullable();
            $table->string('note')->nullable();
            $table->bigInteger('void_id')->nullable();
            $table->string('void_note')->nullable();
            $table->timestamp('void_date')->nullable();
            $table->bigInteger('done_id')->nullable();
            $table->string('done_note')->nullable();
            $table->timestamp('done_date')->nullable();
            $table->string('delete_note')->nullable();
            $table->timestamp('delete_id')->nullable();
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
        Schema::dropIfExists('handover_purchase_invoices');
    }
};
