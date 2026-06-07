<?php

namespace SmartSearch\Tests;

use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;
use SmartSearch\SmartSearchServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            SmartSearchServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app['config']->set('smartsearch.driver', 'database');
        $app['config']->set('smartsearch.fallback', null);
        $app['config']->set('smartsearch.queue', false);
        $app['config']->set('scout.driver', 'database');
    }

    protected function setUpProductTable(): void
    {
        Schema::create('products', function ($table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->text('embedding')->nullable();
        });
    }
}
