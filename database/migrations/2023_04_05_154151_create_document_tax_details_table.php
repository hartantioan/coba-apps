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
        if (!Schema::hasTable('document_tax_details')) {
            Schema::create('document_tax_details', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('document_tax_id')->nullable();
                $table->string('item')->nullable();
                $table->double('price')->nullable();
                $table->double('qty')->nullable();
                $table->double('subtotal')->nullable();
                $table->double('discount')->nullable();
                $table->double('total')->nullable();
                $table->double('tax')->nullable();
                $table->double('nominal_ppnbm')->nullable();
                $table->double('ppnbm')->nullable();
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
        Schema::dropIfExists('document_tax_details');
    }
};
