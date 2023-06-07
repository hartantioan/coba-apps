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
        Schema::create('close_bill_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('close_bill_id')->nullable();
            $table->bigInteger('fund_request_id')->nullable();
            $table->bigInteger('coa_id')->nullable();
            $table->bigInteger('cost_distribution_id')->nullable();
            $table->double('nominal')->nullable();
            $table->bigInteger('tax_id')->nullable();
            $table->char('is_include_tax',1)->nullable();
            $table->double('percent_tax')->nullable();
            $table->bigInteger('wtax_id')->nullable();
            $table->double('percent_wtax')->nullable();
            $table->double('total')->nullable();
            $table->double('tax')->nullable();
            $table->double('wtax')->nullable();
            $table->double('grandtotal')->nullable();
            $table->double('balance')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['close_bill_id','fund_request_id','coa_id','cost_distribution_id'],'close_bill_details_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('close_bill_details');
    }
};
