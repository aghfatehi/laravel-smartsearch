<?php

namespace SmartSearch;

use Illuminate\Support\ServiceProvider;
use SmartSearch\Contracts\SearchDriver;
use SmartSearch\Contracts\SearchManager as SearchManagerContract;

class SmartSearchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/smartsearch.php', 'smartsearch');

        $this->app->singleton(SearchManagerContract::class, function ($app) {
            return new SearchManager($app['config']['smartsearch']);
        });

        $this->app->bind(SearchDriver::class, function ($app) {
            $manager = $app->make(SearchManagerContract::class);
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
