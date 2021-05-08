<?php namespace SunLab\Measures\Updates;

use Schema;
use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;

class CreateListenedEventsTable extends Migration
{
    public function up()
    {
        Schema::create('sunlab_measures_listened_events', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->boolean('active')->default(true);
            $table->string('event_name');
            $table->string('measure_name');
            $table->boolean('on_logged_in_user')->default(false);
            $table->string('model_to_watch')->nullable();
            $table->string('model_to_update')->nullable();
            $table->string('route_parameter')->nullable();
            $table->string('model_attribute')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sunlab_measures_listened_events');
    }
}
