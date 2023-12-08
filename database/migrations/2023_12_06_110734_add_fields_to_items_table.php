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
        Schema::table('items', function (Blueprint $table) {
            $table->bigInteger('type_id')->nullable()->after('status')->index();
            $table->bigInteger('size_id')->nullable()->after('type_id')->index();
            $table->bigInteger('variety_id')->nullable()->after('size_id')->index();
            $table->bigInteger('pattern_id')->nullable()->after('variety_id')->index();
            $table->bigInteger('color_id')->nullable()->after('pattern_id')->index();
            $table->bigInteger('grade_id')->nullable()->after('color_id')->index();
            $table->bigInteger('brand_id')->nullable()->after('grade_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('type_id','size_id','variety_id','pattern_id','color_id','grade_id','brand_id');
        });
    }
};
