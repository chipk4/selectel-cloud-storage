<?php

namespace Chipk4\Selectel;

use Chipk4\Selectel\Models\Container;
use Illuminate\Support\ServiceProvider;

class SelectelApiServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerConfig();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(CloudStorage::class, function () {
            $api = new Api(config('selectel-api'));
            return new CloudStorage($api);
        });

        $this->app->alias(CloudStorage::class, 'selectel-api');
    }

    /**
     * TODO: check for correct values in config file
     */
    protected function registerConfig()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/selectel-api.php', 'selectel-api');

        $this->publishes([
            __DIR__ . '/../config/selectel-api.php' => config_path('selectel-api.php')
        ]);
    }
}