<?php namespace SunLab\Measures\Updates;

use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use Winter\Storm\Support\Facades\Schema;

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
