<?php

namespace Chipk4\Selectel;

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
        $this->mergeConfigFrom(__DIR__.'/../config/selectel-api.php', 'selectel-api');

        $this->publishes([
            __DIR__.'/../config/selectel-api.php' => config_path('selectel-api.php')
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
