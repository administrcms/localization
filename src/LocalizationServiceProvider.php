<?php

namespace Administr\Localization;

use Blade;
use Illuminate\Support\ServiceProvider;

class LocalizationServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../Database/migrations' => database_path('migrations')
        ], 'migrations');

        $this->publishes([
            __DIR__ . '/../Database/seeds' => database_path('seeds')
        ], 'seeds');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $app = $this->app;

        $this->app->bind(Localizator::class, function() use ($app){
            return new Localizator($app['app'], $app['session'], $app['url']);
        });

        $this->app->alias(Localizator::class, 'administr.localizator');
    }
}