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
        Schema::create('user_destination_documents', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->nullable()->index();
            $table->text('address')->nullable();
            $table->bigInteger('country_id')->nullable()->index();
            $table->bigInteger('province_id')->nullable()->index();
            $table->bigInteger('city_id')->nullable()->index();
            $table->bigInteger('district_id')->nullable()->index();
            $table->char('is_default',1)->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_destination_documents');
    }
};
