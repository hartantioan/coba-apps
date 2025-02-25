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
        Schema::table('sample_test_inputs', function (Blueprint $table) {
            $table->renameColumn('price_estimation', 'price_estimation_loco');
            $table->char('type')->nullable();
            $table->decimal('price_estimation_franco')->nullable();
            $table->dropColumn([
                'lab_type',
                'lab_name',
                'wet_whiteness_value',
                'dry_whiteness_value',
                'document_test_result',
                'item_name',
                'test_result_note',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sample_test_inputs', function (Blueprint $table) {
            //
        });
    }
};
