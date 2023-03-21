<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('outgoing_payments'))
        Schema::create('outgoing_payments', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('branch_id')->nullable();
            $table->bigInteger('payment_request_id')->nullable();
            $table->date('posting_date')->nullable();
            $table->date('due_date')->nullable();
            $table->date('document_date')->nullable();
            $table->double('nominal')->nullable();
            $table->bigInteger('coa_id')->nullable();
            $table->string('document')->nullable();
            $table->text('note')->nullable();
            $table->char('status', 1)->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('outgoing_payments');
    }
};
