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
        Schema::table('incoming_payments', function (Blueprint $table) {
            $table->bigInteger('void_id')->after('status')->nullable();
            $table->string('void_note')->after('void_id')->nullable();
            $table->timestamp('void_date')->after('void_note')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incoming_payments', function (Blueprint $table) {
            $table->dropColumn('void_id','void_note','void_date');
        });
    }
};
