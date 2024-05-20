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
        Schema::table('good_scales', function (Blueprint $table) {
            $table->bigInteger('purchase_order_detail_id')->nullable()->after('note_qc');
            $table->bigInteger('item_id')->nullable()->after('purchase_order_detail_id');
            $table->decimal('qty_in',20,5)->nullable()->after('item_id');
            $table->decimal('qty_out',20,5)->nullable()->after('qty_in');
            $table->decimal('qty_balance',20,5)->nullable()->after('qty_out');
            $table->decimal('qty_qc',20,5)->nullable()->after('qty_balance');
            $table->decimal('qty_final',20,5)->nullable()->after('qty_qc');
            $table->bigInteger('item_unit_id')->nullable()->after('qty_final');
            $table->bigInteger('qty_conversion')->nullable()->after('item_unit_id');
            $table->bigInteger('note2')->nullable()->after('note');
            $table->bigInteger('warehouse_id')->nullable()->after('place_id');

            $table->index(['purchase_order_detail_id','item_id','item_unit_id','warehouse_id'],'gs_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('good_scales', function (Blueprint $table) {
            //
        });
    }
};
