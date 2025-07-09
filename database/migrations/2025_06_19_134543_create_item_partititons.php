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
        Schema::create('item_partitions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->bigInteger('user_id')->index(); // Creator
            $table->date('post_date')->nullable();
            $table->text('note')->nullable();
            $table->string('document')->nullable();
            $table->decimal('grandtotal', 20,5)->default(0)->nullable();
            $table->tinyInteger('status')->default(1); // 1=Menunggu, etc.

            // Void tracking
            $table->bigInteger('void_id')->nullable()->index();
            $table->text('void_note')->nullable();
            $table->timestamp('void_date')->nullable();

            // Delete tracking
            $table->bigInteger('delete_id')->nullable()->index();
            $table->text('delete_note')->nullable();

            // Done tracking
            $table->bigInteger('done_id')->nullable()->index();
            $table->timestamp('done_date')->nullable();
            $table->text('done_note')->nullable();

            $table->softDeletes(); // deleted_at
            $table->timestamps();  // created_at & updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_partititons');
    }
};
