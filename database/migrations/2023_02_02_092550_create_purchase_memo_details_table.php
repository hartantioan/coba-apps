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
        if(!Schema::hasTable('purchase_memo_details'))
        Schema::create('purchase_memo_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('purchase_memo_id')->nullable();
            $table->string('lookable_type',155)->nullable();
            $table->bigInteger('lookable_id')->nullable();
            $table->string('description')->nullable();
            $table->char('is_include_tax')->nullable();
            $table->bigInteger('tax_id')->nullable();
            $table->bigInteger('wtax_id')->nullable();
            $table->double('percent_tax')->nullable();
            $table->double('percent_wtax')->nullable();
            $table->double('total')->nullable();
            $table->double('tax')->nullable();
            $table->double('wtax')->nullable();
            $table->double('grandtotal')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['purchase_memo_id', 'lookable_id', 'coa_debit_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_memo_details');
    }
};
