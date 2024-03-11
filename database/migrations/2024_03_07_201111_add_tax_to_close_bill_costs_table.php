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
        Schema::table('close_bill_costs', function (Blueprint $table) {
            $table->renameColumn('nominal','total');
            $table->bigInteger('tax_id')->after('price')->nullable()->after('nominal');
            $table->decimal('percent_tax',20,5)->after('tax_id')->nullable()->after('tax_id');
            $table->char('is_include_tax',1)->after('percent_tax')->nullable()->after('percent_tax');
            $table->bigInteger('wtax_id')->after('is_include_tax')->nullable()->after('is_include_tax');
            $table->decimal('percent_wtax',20,5)->after('wtax_id')->nullable()->after('wtax_id');
            $table->decimal('tax',20,5)->after('total')->nullable()->after('percent_wtax');
            $table->decimal('wtax',20,5)->after('tax')->nullable()->after('tax');
            $table->decimal('grandtotal',20,5)->after('wtax')->nullable()->after('wtax');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('close_bill_costs', function (Blueprint $table) {
            //
        });
    }
};
