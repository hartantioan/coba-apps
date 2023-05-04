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
        if (!Schema::hasTable('approval_table_originators'))
        Schema::create('approval_table_originators', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('approval_table_id')->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['approval_table_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('approval_table_originators');
    }
};
