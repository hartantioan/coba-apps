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
        if(!Schema::hasTable('asset_groups'))
        Schema::create('asset_groups', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 155)->nullable();
            $table->bigInteger('parent_id')->nullable();
            $table->bigInteger('coa_id')->nullable();
            $table->bigInteger('depreciation_coa_id')->nullable();
            $table->bigInteger('cost_coa_id')->nullable();
            $table->integer('depreciation_period')->nullable();
            $table->char('status', 1)->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['parent_id', 'coa_id', 'depreciation_coa_id', 'cost_coa_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('asset_groups');
    }
};
