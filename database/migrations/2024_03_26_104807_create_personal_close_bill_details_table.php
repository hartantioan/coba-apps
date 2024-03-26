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
        Schema::create('personal_close_bill_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('personal_close_bill_id')->nullable();
            $table->bigInteger('fund_request_id')->nullable();
            $table->decimal('nominal',20,5)->nullable();
            $table->string('note')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['personal_close_bill_id','fund_request_id'],'pcbi_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_close_bill_details');
    }
};
