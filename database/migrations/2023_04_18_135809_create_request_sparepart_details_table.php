<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('request_sparepart_details')) {
            Schema::create('request_sparepart_details', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('request_sparepart_id')->nullable();
                $table->bigInteger('equipment_sparepart_id')->nullable();
                $table->double('qty_request')->nullable();
                $table->double('qty_usage')->nullable();
                $table->double('qty_return')->nullable();
                $table->double('qty_repair')->nullable();
                $table->timestamps();
                $table->softDeletes('deleted_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('request_sparepart_details');
    }
};
