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
        Schema::create('delivery_receives', function (Blueprint $table) {
            $table->id();
            $table->string('code',155)->unique();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('account_id')->nullable();
            $table->string('receiver_name')->nullable();
            $table->date('post_date')->nullable();
            $table->date('document_date')->nullable();
            $table->string('delivery_no',155)->nullable();
            $table->string('document')->nullable();
            $table->string('note')->nullable();
            $table->char('status',1)->nullable();
            $table->decimal('total',20,5)->nullable();
            $table->decimal('tax',20,5)->nullable();
            $table->decimal('wtax',20,5)->nullable();
            $table->decimal('grandtotal',20,5)->nullable();
            $table->bigInteger('void_id')->nullable();
            $table->string('void_note')->nullable();
            $table->timestamp('void_date')->nullable();
            $table->softDeletes('deleted_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_receives');
    }
};
