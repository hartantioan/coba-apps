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
        if(!Schema::hasTable('good_returns'))
        Schema::create('good_returns', function (Blueprint $table) {
            $table->id();
            $table->string('code',155)->unique();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('supplier_id')->nullable();
            $table->bigInteger('branch_id')->nullable();
            $table->bigInteger('warehouse_id')->nullable();
            $table->string('document')->nullable();
            $table->date('posting_date')->nullable();
            $table->date('due_date')->nullable();
            $table->date('document_date')->nullable();
            $table->text('note')->nullable();
            $table->char('status',1)->nullable();
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
        Schema::dropIfExists('good_returns');
    }
};
