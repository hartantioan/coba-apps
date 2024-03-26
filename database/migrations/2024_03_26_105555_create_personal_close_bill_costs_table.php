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
        Schema::create('personal_close_bill_costs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('personal_close_bill_id')->nullable();
            $table->string('note')->nullable();
            $table->decimal('qty',20,5)->nullable();
            $table->bigInteger('unit_id')->nullable();
            $table->decimal('price',20,5)->nullable();
            $table->bigInteger('tax_id')->nullable();
            $table->decimal('percent_tax',20,5)->nullable();
            $table->char('is_include_tax',1)->nullable();
            $table->bigInteger('wtax_id')->nullable();
            $table->decimal('percent_wtax',20,5)->nullable();
            $table->decimal('total',20,5)->nullable();
            $table->decimal('tax',20,5)->nullable();
            $table->decimal('wtax',20,5)->nullable();
            $table->decimal('grandtotal',20,5)->nullable();
            $table->bigInteger('place_id')->nullable();
            $table->bigInteger('line_id')->nullable();
            $table->bigInteger('machine_id')->nullable();
            $table->bigInteger('division_id')->nullable();
            $table->bigInteger('project_id')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['personal_close_bill_id','unit_id','tax_id','wtax_id','place_id','line_id','machine_id','division_id','project_id'],'pcbc_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_close_bill_costs');
    }
};
