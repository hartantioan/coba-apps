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
            $table->bigInteger('user_update_id')->nullable()->after('user_id')->index();
            $table->timestamp('update_time')->nullable()->after('user_update_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketing_order_deliveries', function (Blueprint $table) {
            $table->dropColumn('user_update_id','update_time');
        });
    }
};
