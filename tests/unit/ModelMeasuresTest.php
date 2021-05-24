<?php namespace SunLab\Measures\Tests\Unit;

use Backend\Facades\BackendAuth;
use Backend\Models\User;
use SunLab\Measures\Classes\MeasureManager;
use SunLab\Measures\Models\ListenedEvent;
use SunLab\Measures\Models\Measure;
use SunLab\Measures\Tests\MeasuresPluginTestCase;

class ModelMeasuresTest extends MeasuresPluginTestCase
{
    public function testDecrementingAMeasure()
    {
        $this->addUpdateEventToUser();

        $this->createUser();

        $this->updateUserEmailThreeTimes();

        $this->assertEquals(3, $this->user->getAmountOf('user_updated'));

        $this->user->decrementMeasure('user_updated');

        $this->assertEquals(2, $this->user->getAmountOf('user_updated'));
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

    public function testResettingModelMeasures()
    {
        $this->addUpdateEventToUser();

        $this->createUser();

        $this->updateUserEmailThreeTimes();

        $this->assertEquals(3, $this->user->getAmountOf('user_updated'));

        $this->user->resetMeasure('user_updated');

        $this->assertEquals(0, $this->user->getAmountOf('user_updated'));
    }

    public function testResettingMeasuresAtPreciseAmount()
    {
        $this->addUpdateEventToUser();

        $this->createUser();

        $this->updateUserEmailThreeTimes();

        $this->assertEquals(3, $this->user->getAmountOf('user_updated'));

        $this->user->resetMeasure('user_updated', 1);

        $this->assertEquals(1, $this->user->getAmountOf('user_updated'));
    }

    protected function addUpdateEventToUser()
    {
        User::extend(function ($model) {
            $model->bindEvent('model.afterUpdate', function () use ($model) {
                $model->incrementMeasure('user_updated');
            });
        });
    }

    protected function updateUserEmailThreeTimes()
    {
        // Update the model 3 times
        for ($i = 1; $i <= 3; $i++) {
            $this->user->email = "other-email${i}@test.com";
            $this->user->save();
        }
    }
}
