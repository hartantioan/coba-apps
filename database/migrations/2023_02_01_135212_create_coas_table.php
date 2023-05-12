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
        if(!Schema::hasTable('coas'))
        Schema::create('coas', function (Blueprint $table) {
            $table->id();
            $table->string('code', 155)->nullable();
            $table->string('name', 155)->nullable();
            $table->bigInteger('company_id')->nullable();
            $table->bigInteger('parent_id')->nullable();
            $table->integer('level')->nullable();
            $table->char('status', 1)->nullable();
            $table->char('is_confidential', 1)->nullable();
            $table->char('is_control_account', 1)->nullable();
            $table->char('is_cash_account', 1)->nullable();
            $table->char('is_hidden', 1)->nullable();
            $table->char('show_journal',1)->nullable();
            $table->char('bp_journal',1)->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('coas');
    }
};
