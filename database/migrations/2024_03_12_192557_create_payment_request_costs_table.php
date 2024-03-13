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
        Schema::create('payment_request_costs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('payment_request_id')->nullable();
            $table->bigInteger('cost_distribution_id')->nullable();
            $table->bigInteger('coa_id')->nullable();
            $table->bigInteger('place_id')->nullable();
            $table->bigInteger('line_id')->nullable();
            $table->bigInteger('machine_id')->nullable();
            $table->bigInteger('division_id')->nullable();
            $table->bigInteger('project_id')->nullable();
            $table->decimal('nominal_debit',20,5)->nullable();
            $table->decimal('nominal_credit',20,5)->nullable();
            $table->decimal('nominal_debit_fc',20,5)->nullable();
            $table->decimal('nominal_credit_fc',20,5)->nullable();
            $table->string('note')->nullable();
            $table->string('note2')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['payment_request_id', 'cost_distribution_id', 'coa_id', 'place_id', 'line_id', 'machine_id', 'division_id', 'project_id'],'prc_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_request_costs');
    }
};
