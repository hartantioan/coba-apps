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
        if (!Schema::hasTable('request_spareparts')) {
            Schema::create('request_spareparts', function (Blueprint $table) {
                $table->id();
                $table->string('code',155)->unique();
                $table->bigInteger('work_order_id')->nullable();
                $table->bigInteger('user_id')->nullable();
                $table->date('request_date')->nullable();
                $table->text('summary_issue')->nullable();
                $table->char('status', 1)->nullable();
                $table->timestamps();
                $table->softDeletes('deleted_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('request_spareparts');
    }
};
