<?php

declare( strict_types = 1 );

namespace Ocolin\RouterOS\Laravel;

use Ocolin\RouterOS\Client;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;


class ServiceProvider extends LaravelServiceProvider
{
    public function register() : void
    {
        $this->mergeConfigFrom(
            path: __DIR__ . '/../../config/routeros.php', key: 'routeros'
        );

        $this->app->singleton(Client::class, function () {
            return new Client( config(' routeros'));
        });
    }

    public function boot() : void
    {
        $this->publishes([
            __DIR__ . '/../../config/routeros.php' => config_path('routeros.php'),
        ]);
    }
}