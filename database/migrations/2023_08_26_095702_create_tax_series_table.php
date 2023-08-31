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
        Schema::create('tax_series', function (Blueprint $table) {
            $table->id();
            $table->string('code',155)->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('company_id')->nullable();
            $table->string('npwp',155)->nullable();
            $table->string('djp_letter_no')->nullable();
            $table->string('pkp_letter_no')->nullable();
            $table->year('year')->nullable();
            $table->string('document')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('start_no',50)->nullable();
            $table->string('end_no',50)->nullable();
            $table->string('note')->nullable();
            $table->char('status',1)->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['user_id', 'company_id'],'tax_series_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_series');
    }
};
