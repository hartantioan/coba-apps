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
        Schema::table('request_sparepart_details', function (Blueprint $table) {
            $table->decimal('qty_request', 20, 5)->change();
            $table->decimal('qty_usage', 20, 5)->change();
            $table->decimal('qty_return', 20, 5)->change();
            $table->decimal('qty_repair', 20, 5)->change();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_sparepart_details', function (Blueprint $table) {
            //
        });
    }
};
