<?php namespace SunLab\Measures\Tests\Unit;

use Backend\Models\User;
use Illuminate\Support\Facades\Route;
use SunLab\Measures\Models\ListenedEvent;
use Winter\Storm\Support\Facades\Event;

class ListenedEventsTest extends ModelMeasuresTest
{
    public function testIncrementingAMeasureFromAnEvent()
    {
        $this->addUpdateEventToUser();

        $this->createUser();

        $this->user->email = 'other-email@test.com';
        $this->user->save();

        $this->assertEquals(1, $this->user->getAmountOf('user_updated'));
    }

    public function testIncrementingAMeasureFromAListenedEvent()
    {
        $listenedEvent = new ListenedEvent;
        $listenedEvent->event_name = 'model.afterUpdate';
        $listenedEvent->measure_name = 'user_updated';
        $listenedEvent->model_to_watch = User::class;
        $listenedEvent->save();

        $this->getPluginObject()->boot();

        $this->createUser();
        $this->user->email = 'other-email@test.com';
        $this->user->save();

        $this->assertEquals(1, $this->user->getAmountOf('user_updated'));
    }

    public function testRetrievingModelFromURI()
    {
        Route::get('users/{login}', function ($login) {
            Event::fire('event_name');
        });

        $listenedEvent = new ListenedEvent;
        $listenedEvent->event_name = 'event_name';
        $listenedEvent->measure_name = 'measure_name';
        $listenedEvent->model_to_update = User::class;
        $listenedEvent->route_parameter = $listenedEvent->model_attribute = 'login';
        $listenedEvent->save();

        $this->getPluginObject()->boot();

        $this->createUser();

        $this->get('/users/username');

        $this->assertEquals(1, $this->user->getAmountOf('measure_name'));
    }
}
