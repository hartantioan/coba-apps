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
        Schema::create('mitra_customers', function (Blueprint $table) {
            $table->id();                                   //
            $table->string('code')->nullable();
            $table->string('name')->nullable();
            $table->char('type')->nullable()->default('2');
            $table->string('branch')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->bigInteger('province_id')->nullable();
            $table->bigInteger('city_id')->nullable();
            $table->bigInteger('district_id')->nullable();
            $table->string('id_card')->nullable();
            $table->string('pic_name')->nullable();
            $table->string('pic_address')->nullable();
            $table->string('creditlimit')->nullable();
            $table->string('top')->nullable();
            $table->string('npwp')->nullable();
            // $table->text('delivery_address')->nullable();
            $table->char('status_approval', 1)->nullable();                     // 0 pending, 0 approved, 0 rejected
            
            $table->bigInteger('user_id')->nullable()->comment('ID Internal BP');
            $table->bigInteger('mitra_id')->nullable()->comment('ID Broker');
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
        Schema::dropIfExists('mitra_customers');
    }
};
