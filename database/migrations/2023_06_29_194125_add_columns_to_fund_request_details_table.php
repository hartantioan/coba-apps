<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fund_request_details', function (Blueprint $table) {
            $table->bigInteger('tax_id')->after('price')->nullable();
            $table->double('percent_tax')->after('tax_id')->nullable();
            $table->char('is_include_tax',1)->after('percent_tax')->nullable();
            $table->bigInteger('wtax_id')->after('is_include_tax')->nullable();
            $table->double('percent_wtax')->after('wtax_id')->nullable();
            $table->double('tax')->after('total')->nullable();
            $table->double('wtax')->after('tax')->nullable();
            $table->double('grandtotal')->after('wtax')->nullable();

            $table->index(['tax_id','wtax_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fund_request_details', function (Blueprint $table) {
            $table->dropColumn('tax_id','percent_tax','is_include_tax','wtax_id','percent_wtax','tax','wtax','grandtotal');
        });
    }
};
