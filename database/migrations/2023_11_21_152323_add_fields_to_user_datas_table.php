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
        Schema::table('user_datas', function (Blueprint $table) {
            $table->string('npwp',155)->nullable()->after('content');
            $table->string('address')->nullable()->after('npwp');
            $table->bigInteger('country_id')->nullable()->after('address')->index();
            $table->bigInteger('province_id')->nullable()->after('country_id')->index();
            $table->bigInteger('city_id')->nullable()->after('province_id')->index();
            $table->bigInteger('district_id')->nullable()->after('city_id')->index();
            $table->bigInteger('subdistrict_id')->nullable()->after('district_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_datas', function (Blueprint $table) {
            //
        });
    }
};
