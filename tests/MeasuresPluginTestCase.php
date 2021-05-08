<?php namespace SunLab\Measures\Tests;

use Backend\Facades\BackendAuth;
use Backend\Models\User;
use PluginTestCase;

abstract class MeasuresPluginTestCase extends PluginTestCase
{
    protected $refreshPlugins = [
        'SunLab.Measures',
    ];

    protected $user;

    public function setUp(): void
    {
        parent::setUp();

        User::extend(function ($user) {
            $user->extendClassWith('SunLab.Measures.Behaviors.Measurable');
        });
    }

    // Create a base model
    protected function createUser()
    {
        $this->user = BackendAuth::register([
            'login' => 'username',
            'email' => 'user@user.com',
            'password' => 'abcd1234',
            'password_confirmation' => 'abcd1234'
        ]);
    }
}
