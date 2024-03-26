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
        Schema::create('personal_close_bills', function (Blueprint $table) {
            $table->id();
            $table->string('code',155)->unique();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('company_id')->nullable();
            $table->date('post_date')->nullable();
            $table->string('note')->nullable();
            $table->char('status',1)->nullable();
            $table->bigInteger('currency_id')->nullable();
            $table->decimal('currency_rate',20,5)->nullable();
            $table->string('document_no', 155)->nullable();
            $table->date('document_date')->nullable();
            $table->string('tax_no',155)->nullable();
            $table->string('tax_cut_no',155)->nullable();
            $table->date('cut_date')->nullable();
            $table->string('spk_no',155)->nullable();
            $table->string('invoice_no',155)->nullable();
            $table->decimal('total',20,5)->nullable();
            $table->decimal('tax',20,5)->nullable();
            $table->decimal('wtax',20,5)->nullable();
            $table->decimal('grandtotal',20,5)->nullable();
            $table->bigInteger('void_id')->nullable();
            $table->string('void_note')->nullable();
            $table->timestamp('void_date')->nullable();
            $table->bigInteger('delete_id')->nullable();
            $table->string('delete_note')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['user_id','company_id','currency_id','void_id','delete_id'],'pcb_header_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_close_bills');
    }
};
