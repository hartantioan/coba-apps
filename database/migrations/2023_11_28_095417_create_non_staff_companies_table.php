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
        Schema::create('non_staff_companies', function (Blueprint $table) {
            $table->id();
            $table->string('code',155)->unique();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('account_id')->nullable();
            $table->bigInteger('vendor_id')->nullable();
            $table->date('post_date')->nullable();
            $table->string('document_no',155)->nullable();
            $table->string('note')->nullable();
            $table->char('status', 1)->nullable();
            $table->softDeletes('deleted_at');
            $table->timestamps();

            $table->index(['user_id','account_id','vendor_id'],'non_staff_company_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('non_staff_companies');
    }
};
