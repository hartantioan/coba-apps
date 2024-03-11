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
        Schema::table('close_bills', function (Blueprint $table) {
            $table->dropColumn('nominal','balance');
            $table->bigInteger('delete_id')->nullable()->after('void_date');
            $table->string('delete_note')->nullable()->after('delete_id');
            $table->decimal('total',20,5)->change();
            $table->decimal('tax',20,5)->change();
            $table->decimal('wtax',20,5)->change();
            $table->decimal('grandtotal',20,5)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('close_bills', function (Blueprint $table) {
            //
        });
    }
};
