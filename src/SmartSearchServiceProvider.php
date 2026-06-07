<?php

namespace SmartSearch;

use Illuminate\Support\ServiceProvider;
use SmartSearch\Contracts\SearchDriver;
use SmartSearch\Contracts\SmartSearchManager as SmartSearchManagerContract;

class SmartSearchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/smartsearch.php', 'smartsearch');

        $this->app->singleton(SmartSearchManagerContract::class, function ($app) {
            return new SmartSearchManager($app['config']['smartsearch']);
        });

        $this->app->bind(SearchDriver::class, function ($app) {
            $manager = $app->make(SmartSearchManagerContract::class);
            return $manager->driver();
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/smartsearch.php' => config_path('smartsearch.php'),
            ], 'smartsearch-config');
        }
    }
}
