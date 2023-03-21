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
        if(!Schema::hasTable('approval_tables'))
        Schema::create('approval_tables', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->bigInteger('approval_id')->nullable();
            $table->bigInteger('menu_id')->nullable();
            $table->integer('level')->nullable();
            $table->char('is_check_nominal',1)->nullable();
            $table->char('sign',2)->nullable();
            $table->double('nominal')->nullable();
            $table->char('status',1)->nullable();
            $table->integer('min_approve')->nullable();
            $table->integer('min_reject')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['approval_id', 'user_id', 'position_id', 'menu_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('approval_tables');
    }
};
