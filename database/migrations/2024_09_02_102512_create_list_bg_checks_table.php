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
        Schema::create('list_bg_checks', function (Blueprint $table) {
            $table->id();
            $table->string('code',155)->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('account_id')->nullable();
            $table->bigInteger('company_id')->nullable();
            $table->date('post_date')->nullable();
            $table->date('valid_until_date')->nullable();
            $table->date('pay_date')->nullable();
            $table->text('bank_source_name')->nullable();
            $table->text('bank_source_no')->nullable();
            $table->text('document_no')->nullable();
            $table->text('document')->nullable();
            $table->text('note')->nullable();
            $table->decimal('nominal',20,5)->nullable();
            $table->decimal('grandtotal',20,5)->nullable();
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
        Schema::dropIfExists('list_bg_checks');
    }
};
