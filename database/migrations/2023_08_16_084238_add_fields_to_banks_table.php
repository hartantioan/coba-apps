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
        Schema::table('banks', function (Blueprint $table) {
            $table->string('account_name',155)->nullable()->after('name');
            $table->string('account_no',50)->nullable()->after('account_name');
            $table->bigInteger('company_id')->nullable()->after('account_no');
            $table->string('branch',155)->nullable()->after('company_id');
            $table->char('is_show',1)->nullable()->after('branch');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('banks', function (Blueprint $table) {
            $table->dropColumn('account_name','account_no','company_id','branch','is_show');
        });
    }
};
