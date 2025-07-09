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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();// Auto-incrementing primary key
            $table->string('code')->unique(); // Unique supplier code
            $table->bigInteger('user_id')->nullable();
            $table->string('name')->nullable(); // Foreign key to users
            $table->string('no_telp')->nullable(); // Phone number
            $table->text('address')->nullable(); // Address
            $table->bigInteger('group_id')->nullable();
            $table->decimal('total', 20, 5)->default(0); // Total amount (with 2 decimal places)
            $table->char('status')->nullable();
            $table->softDeletes(); // deleted_at (for SoftDeletes
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
