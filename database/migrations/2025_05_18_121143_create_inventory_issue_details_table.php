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
        Schema::create('inventory_issue_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('inventory_issue_id')->index();      // Link to InventoryIssue
            $table->bigInteger('item_stock_new_id')->index();       // Source item stock
            $table->bigInteger('store_item_stock_id')->nullable();  // Target/store stock (after issue)

            $table->decimal('qty', 20,5)->default(0);
            $table->decimal('price', 20, 5)->default(0);
            $table->decimal('total', 20,5)->default(0);

            $table->decimal('qty_store_item', 20,5)->default(0); // Quantity to store (can differ from issued qty)
            $table->text('note')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_issue_details');
    }
};
