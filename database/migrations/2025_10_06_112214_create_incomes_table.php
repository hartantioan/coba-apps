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
        Schema::create('incomes', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->nullable();
            $table->string('code', 50)->unique();
            $table->string('document', 10000)->nullable();
            $table->date('post_date')->nullable();
            $table->string('note')->nullable();
            $table->decimal('grandtotal', 20,5)->default(0)->nullable();
            $table->timestamps();
            $table->softDeletes(); // deleted_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incomes');
    }
};
