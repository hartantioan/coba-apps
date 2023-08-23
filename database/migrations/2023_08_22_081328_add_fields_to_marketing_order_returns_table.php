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
        Schema::table('marketing_order_returns', function (Blueprint $table) {
            $table->bigInteger('account_id')->nullable()->after('user_id');
            $table->bigInteger('company_id')->nullable()->after('account_id');
            $table->date('post_date')->nullable()->after('company_id');
            $table->double('total')->nullable()->after('note');
            $table->double('rounding')->nullable()->after('tax');
            $table->bigInteger('void_id')->nullable()->after('status');
            $table->string('void_note')->nullable()->after('void_id');
            $table->timestamp('void_date')->nullable()->after('void_note');

            $table->index(['user_id','account_id','company_id'],'moreturn_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketing_order_returns', function (Blueprint $table) {
            //
        });
    }
};
