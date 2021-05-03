<?php namespace SunLab\Measures\Updates;

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use October\Rain\Support\Facades\Schema;

class CreateMeasuresTable extends Migration
{
    public function up()
    {
        Schema::create('sunlab_measures_measures', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->timestamps();

            $table->string('name');
            $table->unsignedBigInteger('amount')->default(0);

            $table->unsignedBigInteger('measurable_id');
            $table->string('measurable_type');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sunlab_measures_measures');
    }
}
