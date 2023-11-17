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
        Schema::table('good_receives', function (Blueprint $table) {
            $table->bigInteger('marketing_order_return_id')->nullable()->after('status')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('good_receives', function (Blueprint $table) {
            $table->dropColumn('marketing_order_return_id');
        });
    }
};
