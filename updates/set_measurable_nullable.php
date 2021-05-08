<?php namespace SunLab\Measures\Updates;

use Winter\Storm\Support\Facades\Schema;
use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;

class SetMeasurableNullable extends Migration
{
    public function up()
    {
        Schema::table('sunlab_measures_measures', function (Blueprint $table) {
            $table->unsignedBigInteger('measurable_id')->nullable()->change();
            $table->string('measurable_type')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('sunlab_measures_measures', function (Blueprint $table) {
            $table->unsignedBigInteger('measurable_id')->change();
            $table->string('measurable_type')->change();
        });
    }
}
