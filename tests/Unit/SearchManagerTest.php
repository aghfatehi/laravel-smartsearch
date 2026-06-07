<?php

namespace SmartSearch\Tests\Unit;

use SmartSearch\Builders\SearchQueryBuilder;
use SmartSearch\Contracts\SearchManager as SearchManagerContract;
use SmartSearch\Drivers\DatabaseDriver;
use SmartSearch\SearchManager;
use SmartSearch\Tests\Stubs\Product;
use SmartSearch\Tests\TestCase;

class SearchManagerTest extends TestCase
{
    public function test_for_returns_query_builder(): void
    {
        $manager = app(SearchManagerContract::class);
        $builder = $manager->for(Product::class);

        $this->assertInstanceOf(SearchQueryBuilder::class, $builder);
        $this->assertEquals(Product::class, $builder->modelClass);
    }

    public function test_for_with_fluent_api_returns_results(): void
    {
        $this->setUpProductTable();

        Product::create(['name' => 'iPhone', 'description' => 'Phone', 'price' => 5000]);

        $manager = app(SearchManagerContract::class);
        $results = $manager->for(Product::class)
            ->query('iPhone')
            ->get();

        $this->assertCount(1, $results);
    }

    public function test_driver_resolves_database(): void
    {
        $manager = new SearchManager([
            'driver' => 'database',
            'fallback' => null,
            'elasticsearch' => ['hosts' => ['localhost:9200']],
        ]);

        $driver = $manager->driver('database');
        $this->assertInstanceOf(DatabaseDriver::class, $driver);
    }

    public function test_fallback_is_null_when_same_as_primary(): void
    {
        $manager = new SearchManager([
            'driver' => 'database',
            'fallback' => 'database',
            'elasticsearch' => ['hosts' => ['localhost:9200']],
        ]);

        $builder = $manager->for(Product::class);
        $this->assertInstanceOf(SearchQueryBuilder::class, $builder);
    }

    public function test_unknown_driver_throws_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $manager = new SearchManager([
            'driver' => 'nonexistent',
            'fallback' => null,
            'elasticsearch' => ['hosts' => ['localhost:9200']],
        ]);

        $manager->driver('nonexistent');
    }
}
