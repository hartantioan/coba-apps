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
        Schema::create('taxes', function (Blueprint $table) {
            $table->id();
            $table->char('transaction_code')->nullable();
            $table->char('replace')->nullable();
            $table->string('code')->nullable();
            $table->date('date')->nullable();
            $table->string('npwp_number')->nullable();
            $table->string('npwp_name')->nullable();
            $table->string('npwp_address')->nullable();
            $table->string('npwp_target')->nullable();
            $table->string('npwp_target_name')->nullable();
            $table->string('npwp_target_address')->nullable();
            $table->double('total')->nullable();
            $table->double('tax')->nullable();
            $table->double('wtax')->nullable();
            $table->string('approval_status')->nullable();
            $table->string('tax_status')->nullable();
            $table->string('reference')->nullable();
            $table->string('url')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('taxes');
    }
};
