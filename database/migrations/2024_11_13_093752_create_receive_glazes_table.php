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
        Schema::create('receive_glazes', function (Blueprint $table) {
            $table->id();
            $table->string('code',155)->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('company_id')->nullable();
            $table->bigInteger('place_id')->nullable();
            $table->bigInteger('line_id')->nullable();
            $table->string('document')->nullable();
            $table->text('note')->nullable();
            $table->bigInteger('item_id')->nullable();
            $table->decimal('qty', 20, 5)->nullable();
            $table->bigInteger('to_place_id')->nullable();
            $table->bigInteger('to_warehouse_id')->nullable();
            $table->char('status',1)->nullable();
            $table->date('post_date')->nullable();
            $table->bigInteger('void_id')->nullable();
            $table->string('void_note')->nullable();
            $table->timestamp('void_date')->nullable();

            $table->bigInteger('delete_id')->nullable();
            $table->string('delete_note')->nullable();
            $table->timestamp('delete_date')->nullable();
            $table->bigInteger('done_id')->nullable();
            $table->string('done_note')->nullable();
            $table->timestamp('done_date')->nullable();
            $table->decimal('grandtotal', 20, 5)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receive_glazes');
    }
};
