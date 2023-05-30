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
        if(!Schema::hasTable('purchase_invoice_details'))
        Schema::create('purchase_invoice_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('purchase_invoice_id')->nullable();
            $table->string('lookable_type',155)->nullable();
            $table->bigInteger('lookable_id')->nullable();
            $table->double('qty')->nullable();
            $table->double('price')->nullable();
            $table->double('total')->nullable();
            $table->bigInteger('tax_id')->nullable();
            $table->bigInteger('wtax_id')->nullable();
            $table->char('is_include_tax',1)->nullable();
            $table->double('percent_tax')->nullable();
            $table->double('tax')->nullable();
            $table->double('percent_wtax')->nullable();
            $table->double('wtax')->nullable();
            $table->double('grandtotal')->nullable();
            $table->string('note')->nullable();
            $table->bigInteger('place_id')->nullable();
            $table->bigInteger('line_id')->nullable();
            $table->bigInteger('department_id')->nullable();
            $table->bigInteger('warehouse_id')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['purchase_invoice_id', 'lookable_id', 'tax_id', 'wtax_id', 'place_id', 'line_id', 'department_id', 'warehouse_id'],'purchase_invoice_detail_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_invoice_details');
    }
};
