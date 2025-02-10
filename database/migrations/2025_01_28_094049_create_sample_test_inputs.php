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
        Schema::create('sample_test_inputs', function (Blueprint $table) {
            $table->id();
            $table->string('code',155)->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('sample_type_id')->nullable();
            $table->bigInteger('province_id')->nullable();
            $table->bigInteger('city_id')->nullable();
            $table->bigInteger('subdistrict_id')->nullable();
            $table->text('village_name')->nullable();
            $table->text('supplier')->nullable();
            $table->text('supplier_name')->nullable();
            $table->text('supplier_phone')->nullable();
            $table->date('sample_date')->nullable();
            $table->date('post_date')->nullable();
            $table->text('link_map')->nullable();
            $table->string('permission_type')->nullable();
            $table->string('permission_name')->nullable();
            $table->string('commodity_permits')->nullable();
            $table->string('permits_period')->nullable();
            $table->decimal('receivable_capacity')->nullable();
            $table->decimal('price_estimation')->nullable();
            $table->string('supplier_sample_code')->nullable();
            $table->string('company_sample_code')->nullable();
            $table->text('document')->nullable();
            $table->text('note')->nullable();
            $table->char('lab_type')->nullable();
            $table->text('lab_name')->nullable();
            $table->text('wet_whiteness_value')->nullable();
            $table->text('dry_whiteness_value')->nullable();
            $table->text('document_test_result')->nullable();
            $table->text('item_name')->nullable();
            $table->text('test_result_note')->nullable();
            $table->char('status', 1)->nullable();
            $table->softDeletes('deleted_at');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sample_test_inputs');
    }
};
