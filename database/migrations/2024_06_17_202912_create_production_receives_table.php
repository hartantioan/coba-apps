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
        Schema::create('production_receives', function (Blueprint $table) {
            $table->id();
            $table->string('code',155)->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('company_id')->nullable();
            $table->bigInteger('production_order_id')->nullable();
            $table->bigInteger('place_id')->nullable();
            $table->bigInteger('shift_id')->nullable();
            $table->string('group',155)->nullable();
            $table->bigInteger('line_id')->nullable();
            $table->bigInteger('machine_id')->nullable();
            $table->date('post_date')->nullable();
            $table->timestamp('start_process_time')->nullable();
            $table->timestamp('end_process_time')->nullable();
            $table->string('document')->nullable();
            $table->bigInteger('status')->nullable();
            $table->bigInteger('void_id')->nullable();
            $table->string('void_note')->nullable();
            $table->timestamp('void_date')->nullable();
            $table->bigInteger('delete_id')->nullable();
            $table->string('delete_note')->nullable();
            $table->bigInteger('done_id')->nullable();
            $table->string('done_note')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['user_id','company_id','production_order_id','place_id','shift_id','line_id','machine_id','void_id','delete_id','done_id'],'pr_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_receives');
    }
};
