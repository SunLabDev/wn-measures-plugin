<?php namespace Winter\User\Tests\Unit\Facades;

use Backend\Facades\BackendAuth;
use Backend\Models\User;
use Illuminate\Support\Facades\Route;
use SunLab\Measures\Classes\MeasureManager;
use SunLab\Measures\Models\ListenedEvent;
use SunLab\Measures\Models\Measure;
use SunLab\Measures\Tests\MeasuresPluginTestCase;
use Winter\Storm\Support\Facades\Event;

class MeasuresTest extends MeasuresPluginTestCase
{
    public function testIncrementingAMeasureFromAnEvent()
    {
        User::extend(function ($model) {
            $model->bindEvent('model.afterUpdate', function () use ($model) {
                $model->incrementMeasure('user_updated');
            });
        });

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

    public function testBulkIncrementing()
    {
        // We truncate because winter:up create an admin
        User::truncate();
        $user = BackendAuth::register([
            'login' => 'username',
            'email' => 'user@user.com',
            'password' => 'abcd1234',
            'password_confirmation' => 'abcd1234'
        ]);
        $user2 = BackendAuth::register([
            'login' => 'username2',
            'email' => 'user@user2.com',
            'password' => 'abcd1234',
            'password_confirmation' => 'abcd1234'
        ]);
        $user3 = BackendAuth::register([
            'login' => 'username3',
            'email' => 'user@user3.com',
            'password' => 'abcd1234',
            'password_confirmation' => 'abcd1234'
        ]);

        $usersBuilder = User::query();

        MeasureManager::incrementMeasure($usersBuilder, 'measure_name');

        $this->assertEquals(3, Measure::query()->count());

        $this->assertEquals(1, $user->getAmountOf('measure_name'));
        $this->assertEquals(1, $user2->getAmountOf('measure_name'));
        $this->assertEquals(1, $user3->getAmountOf('measure_name'));
    }

    public function testIncrementingOrphanMeasure()
    {
        MeasureManager::incrementMeasure('orphan_measure');

        $this->assertEquals(1, MeasureManager::amountOf('orphan_measure'));
    }
}
