<?php namespace SunLab\Measures\Updates;

use October\Rain\Support\Facades\Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

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
