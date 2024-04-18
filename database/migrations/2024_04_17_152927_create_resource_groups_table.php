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
        Schema::create('resource_groups', function (Blueprint $table) {
            $table->id();
            $table->string('code',155)->nullable();
            $table->string('name')->nullable();
            $table->string('other_name')->nullable();
            $table->bigInteger('parent_id')->nullable();
            $table->bigInteger('coa_id')->nullable();
            $table->char('status',1)->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['parent_id','coa_id'],'resource_group_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resource_groups');
    }
};
