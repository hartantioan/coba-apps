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
        Schema::table('marketing_order_delivery_processes', function (Blueprint $table) {
            $table->double('rounding')->after('tax')->nullable();
            $table->string('document')->nullable()->after('status_tracking');
            $table->date('return_date')->nullable()->after('post_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketing_order_delivery_processes', function (Blueprint $table) {
            $table->dropColumn('rounding','document','post_date');
        });
    }
};
