<?php namespace SunLab\Measures;

use Backend;
use Prophecy\Exception\Doubler\ClassNotFoundException;
use SunLab\Measures\Models\ListenedEvent;
use System\Classes\PluginBase;
use System\Classes\SettingsManager;
use Winter\Storm\Exception\ApplicationException;
use Winter\Storm\Support\Facades\Event;
use Winter\Storm\Support\Facades\Str;
use Winter\User\Facades\Auth;

class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'Measures',
            'description' => 'sunlab.measures::lang.plugin.description',
            'author'      => 'SunLab',
            'icon'        => 'icon-sort-alpha-desc',
            'homepage'    => 'https://sunlab.dev'
        ];
    }

    public function boot()
    {
        if ($this->app->runningInConsole() && !$this->app->runningUnitTests()) {
            return;
        }

        $watchedEvents = ListenedEvent::query()
                                     ->where('active', true)
                                     ->get();

        // Create generic event for each watched event
        $watchedEvents->each(function ($event) {

            // Model events are fired locally, we need to bind into them to catch them
            if (Str::startsWith($event->event_name, 'model')) {
                $event->model_to_watch::extend(function ($model) use ($event) {
                    $model->bindEvent($event->event_name, function () use ($model, $event) {

                        // If the measure should be increment on logged in user
                        if ($event->on_logged_in_user) {
                            Auth::user()->incrementMeasure($event->measure_name);
                        } // If we specified a model classname to update
                        elseif ($event->model_to_update) {
                            throw_unless(
                                class_exists($event->model_to_update),
                                new ClassNotFoundException(
                                    "{$event->model_to_update} not found",
                                    $event->model_to_update
                                )
                            );

                            $routeIdentifier = request()->get($event->route_parameter);
                            $event->model_to_update::query()
                                ->where($event->model_attribute, $routeIdentifier)
                                ->firstOrFail();
                        } // Else, default to the model which is firing the event
                        else {
                            $model->incrementMeasure($event->measure_name);
                        }
                    });
                });
            } else {
                Event::listen($event->event_name, function () use ($event) {
                    // If the measure should be increment on logged in user
                    if ($event->on_logged_in_user) {
                        Auth::user()->incrementMeasure($event->measure_name);
                    }

                    // If we specified a model classname to update
                    if ($event->model_to_update) {
                        throw_unless(
                            class_exists($event->model_to_update),
                            new ClassNotFoundException(
                                "{$event->model_to_update} not found",
                                $event->model_to_update
                            )
                        );

                        $routeIdentifier = request($event->route_parameter);

                        throw_if(
                            blank($routeIdentifier),
                            new ApplicationException($event->route_parameter . ' parameter not found in URI.')
                        );

                        $model = $event->model_to_update::query()
                            ->where($event->model_attribute, $routeIdentifier)
                            ->firstOrFail();

                        throw_unless(
                            $model->isClassExtendedWith('SunLab.Measures.Behaviors.Measurable'),
                            new ApplicationException(
                                get_class($model) . ' should implements SunLab\Measures\Behaviors\Measurable.'
                            )
                        );

                        $model->incrementMeasure($event->measure_name);
                    }
                });
            }
        });
    }

    public function registerPermissions()
    {
        return [
            'sunlab.measures.access_settings' => [
                'tab' => 'Measures',
                'label' => 'sunlab.measures::lang.permission.label'
            ],
        ];
    }

    public function registerSettings()
    {
        return [
            'measures' => [
                'label'       => 'Listened Events',
                'description' => 'sunlab.measures::lang.settings.description',
                'category'    => SettingsManager::CATEGORY_SYSTEM,
                'icon'        => 'icon-heartbeat',
                'url'         => Backend::url('sunlab/measures/listenedevents'),
                'order'       => 500,
                'keywords'    => 'credentials api key',
                'permissions' => ['sunlab.measures.access_settings']
            ]
        ];
    }
}
