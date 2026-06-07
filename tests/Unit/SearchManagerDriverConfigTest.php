<?php

namespace SmartSearch\Tests\Unit;

use SmartSearch\Drivers\DatabaseDriver;
use SmartSearch\Facades\Search;
use SmartSearch\Tests\Stubs\Product;
use SmartSearch\Tests\TestCase;

class SearchManagerDriverConfigTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpProductTable();
    }

    public function test_database_driver_resolves_without_extra_config(): void
    {
        $this->app['config']->set('smartsearch.driver', 'database');

        $driver = Search::driver();
        $this->assertInstanceOf(DatabaseDriver::class, $driver);
        $this->assertEquals('database', $driver->getName());
    }

    public function test_database_driver_returns_results(): void
    {
        $this->app['config']->set('smartsearch.driver', 'database');

        Product::create(['name' => 'iPhone 15', 'description' => 'Apple phone', 'price' => 5000]);

        $results = Search::for(Product::class)->query('iPhone')->get();

        $this->assertCount(1, $results);
        $this->assertEquals('iPhone 15', $results->first()->name);
    }

    public function test_database_driver_fallback_disabled_when_same_as_primary(): void
    {
        $this->app['config']->set('smartsearch.driver', 'database');
        $this->app['config']->set('smartsearch.fallback', 'database');

        Product::create(['name' => 'Test', 'description' => 'Desc', 'price' => 100]);

        $results = Search::for(Product::class)->query('Test')->get();

        $this->assertCount(1, $results);
    }

    public function test_elasticsearch_driver_resolves_when_installed(): void
    {
        if (!class_exists(\Elastic\Elasticsearch\ClientBuilder::class)) {
            $this->markTestSkipped('elasticsearch/elasticsearch not installed');
        }

        $this->app['config']->set('smartsearch.driver', 'elasticsearch');

        $driver = Search::driver();
        $this->assertEquals('elasticsearch', $driver->getName());
    }

    public function test_opensearch_driver_throws_when_not_installed(): void
    {
        $this->app['config']->set('smartsearch.driver', 'opensearch');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('opensearch-project/opensearch-php');

        Search::driver();
    }

    public function test_opensearch_driver_throws_on_search_when_not_installed(): void
    {
        $this->app['config']->set('smartsearch.driver', 'opensearch');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('opensearch-project/opensearch-php');

        Search::for(Product::class)->query('test')->get();
    }

    public function test_scout_driver_resolves_when_installed(): void
    {
        if (!class_exists(\Laravel\Scout\EngineManager::class)) {
            $this->markTestSkipped('laravel/scout not installed');
        }

        $this->app['config']->set('smartsearch.driver', 'scout');

        $driver = Search::driver();
        $this->assertEquals('scout', $driver->getName());
    }

    public function test_unknown_driver_throws_exception(): void
    {
        $this->app['config']->set('smartsearch.driver', 'nonexistent');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown search driver: nonexistent');

        Search::driver();
    }
}
