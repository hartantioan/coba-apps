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
        Schema::table('marketing_order_deliveries', function (Blueprint $table) {
            $table->dropColumn('branch_id','warehouse_id','driver_name','driver_phone','receiver_name','receiver_phone','received_date','due_date','due_date_tt','document_received','document_tt','subtotal','tax','grandtotal');
            $table->bigInteger('company_id')->nullable()->after('user_id');
            $table->bigInteger('account_id')->nullable()->after('company_id');
            $table->date('post_date')->nullable()->after('marketing_order_id');
            $table->bigInteger('void_id')->nullable()->after('status');
            $table->string('void_note')->nullable()->after('void_id');
            $table->timestamp('void_date')->nullable()->after('void_date');
            $table->index(['user_id','company_id','account_id','marketing_order_id'],'mod_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('marketing_order_deliveries', function (Blueprint $table) {
            //
        });
    }
};
