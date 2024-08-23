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
        Schema::table('marketing_order_deliveries', function (Blueprint $table) {
            $table->bigInteger('city_id')->nullable()->after('destination_address')->index();
            $table->bigInteger('district_id')->nullable()->after('city_id')->index();
            $table->bigInteger('transportation_id')->nullable()->after('district_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketing_order_deliveries', function (Blueprint $table) {
            $table->dropColumn('province_id','city_id','district_id','transportation_id');
        });
    }
};
