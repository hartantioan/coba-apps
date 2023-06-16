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
        Schema::create('taxes', function (Blueprint $table) {
            $table->id();
            $table->string('code',155)->unique();
            $table->string('name')->nullable();
            $table->bigInteger('coa_purchase_id')->nullable();
            $table->bigInteger('coa_sale_id')->nullable();
            $table->char('type',1)->nullable();
            $table->double('percentage')->nullable();
            $table->char('is_default',1)->nullable();
            $table->char('status',1)->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['coa_purchase_id','coa_sale_id'],'taxes_indexes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('taxes');
    }
};
