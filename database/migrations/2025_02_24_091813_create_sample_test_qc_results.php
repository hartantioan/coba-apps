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
        Schema::create('sample_test_qc_results', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('sample_test_input_id')->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->text('wet_whiteness_value')->nullable();
            $table->text('dry_whiteness_value')->nullable();
            $table->text('document')->nullable();
            $table->text('item_name')->nullable();
            $table->text('note')->nullable();
            $table->char('status', 1)->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sample_test_qc_results');
    }
};
