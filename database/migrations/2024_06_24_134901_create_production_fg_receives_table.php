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
        Schema::create('production_fg_receives', function (Blueprint $table) {
            $table->id();
            $table->string('code',155)->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('company_id')->nullable();
            $table->bigInteger('production_order_id')->nullable();
            $table->bigInteger('place_id')->nullable();
            $table->bigInteger('line_id')->nullable();
            $table->bigInteger('shift_id')->nullable();
            $table->string('group',155)->nullable();
            $table->char('uom_type',1)->nullable(); 
            $table->string('shading',50)->nullable();
            $table->bigInteger('grade_id')->nullable();
            $table->bigInteger('item_unit_id')->nullable();
            $table->decimal('qty',20,5)->nullable();
            $table->date('post_date')->nullable();
            $table->string('document')->nullable();
            $table->char('status',1)->nullable();
            $table->bigInteger('void_id')->nullable();
            $table->string('void_note')->nullable();
            $table->timestamp('void_date')->nullable();
            $table->bigInteger('delete_id')->nullable();
            $table->string('delete_note')->nullable();
            $table->bigInteger('done_id')->nullable();
            $table->string('done_note')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['user_id','company_id','production_order_id','place_id','shift_id','line_id','grade_id','item_unit_id','void_id','delete_id','done_id'],'pfr_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_fg_receives');
    }
};
