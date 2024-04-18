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
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->string('code',155)->nullable();
            $table->string('name')->nullable();
            $table->string('other_name')->nullable();
            $table->bigInteger('resource_group_id')->nullable();
            $table->bigInteger('uom_unit')->nullable();
            $table->decimal('qty',20,5)->nullable();
            $table->decimal('cost',20,5)->nullable();
            $table->bigInteger('place_id')->nullable();
            $table->char('status',1)->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            $table->index(['resource_group_id','uom_unit','place_id'],'resource_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resources');
    }
};
