<?php

use Doctrine\DBAL\Schema\Index;
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
        Schema::create('production_barcodes', function (Blueprint $table) {
            $table->id();
            $table->string('code',155)->nullable();
            $table->bigInteger('user_id')->nullable()->index();
            $table->bigInteger('company_id')->nullable()->index();
            $table->bigInteger('production_order_detail_id')->nullable()->index();
            $table->bigInteger('item_id')->nullable()->index();
            $table->bigInteger('place_id')->nullable()->index();
            $table->bigInteger('line_id')->nullable()->index();
            $table->bigInteger('shift_id')->nullable()->index();
            $table->string('group',50)->nullable()->index();
            $table->date('post_date')->nullable();
            $table->text('note')->nullable();
            $table->char('status',1)->nullable();
            $table->bigInteger('void_id')->nullable()->index();
            $table->string('void_note')->nullable();
            $table->timestamp('void_date')->nullable();
            $table->bigInteger('delete_id')->nullable()->index();
            $table->string('delete_note')->nullable();
            $table->bigInteger('done_id')->nullable()->index();
            $table->string('done_note')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_barcodes');
    }
};
