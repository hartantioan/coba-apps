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
        if(!Schema::hasTable('inventory_transfer_ins'))
        Schema::create('inventory_transfer_ins', function (Blueprint $table) {
            $table->id();
            $table->string('code',155)->unique();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('company_id')->nullable();
            $table->bigInteger('inventory_transfer_out_id')->nullable();
            $table->date('post_date')->nullable();
            $table->string('document')->nullable();
            $table->text('note')->nullable();
            $table->char('status',1)->nullable();
            $table->bigInteger('void_id')->nullable();
            $table->string('void_note')->nullable();
            $table->timestamp('void_date')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['user_id', 'company_id', 'inventory_transfer_out_id'],'inventory_transfer_in_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventory_transfer_ins');
    }
};
