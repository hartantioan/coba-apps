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
        Schema::dropIfExists('marketing_order_delivery_stocks');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
