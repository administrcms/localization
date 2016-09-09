<?php

namespace Administr\Localization;

use Administr\Localization\Commands\MakeAdminModel;
use Blade;
use Illuminate\Support\ServiceProvider;

class LocalizationServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/Database/migrations' => database_path('migrations')
        ], 'migrations');

        $this->publishes([
            __DIR__ . '/Database/seeds' => database_path('seeds')
        ], 'seeds');

        $this->publishes([
            __DIR__ . '/Config/localization.php' => config_path('administr.localization.php')
        ], 'config');
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
            return new Localizator($app['app'], $app['session.store'], $app['url']);
        });

        $this->commands([
           MakeAdminModel::class,
        ]);

        $this->app->alias(Localizator::class, 'administr.localizator');

        $this->mergeConfigFrom(__DIR__ . '/Config/localization.php', 'administr.localization');
    }
}