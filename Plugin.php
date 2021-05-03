<?php namespace SunLab\Measures;

use Backend;
use System\Classes\PluginBase;

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
}
