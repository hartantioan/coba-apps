<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
        public function up()
    {
        Schema::table('rule_bp_scale', function (Blueprint $table) {
            $table->renameColumn('water_percent', 'percentage_level');
        });
    }

    public function down()
    {
        Schema::table('rule_bp_scale', function (Blueprint $table) {
            $table->renameColumn('percentage_level', 'water_percent');
        });
    }

};
