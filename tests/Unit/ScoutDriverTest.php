<?php

namespace SmartSearch\Tests\Unit;

use SmartSearch\Builders\SearchQueryBuilder;
use SmartSearch\Drivers\ScoutDriver;
use SmartSearch\Tests\Stubs\ScoutProduct;
use SmartSearch\Tests\TestCase;

class ScoutDriverTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpProductTable();
    }

    public function test_search_returns_collection(): void
    {
        ScoutProduct::create(['name' => 'iPhone', 'description' => 'Phone', 'price' => 5000]);

        $driver = new ScoutDriver();
        $builder = new SearchQueryBuilder(ScoutProduct::class, $driver);
        $builder->query('iPhone');

        $results = $driver->search($builder);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $results);
        $this->assertGreaterThanOrEqual(1, $results->count());
    }

    public function test_search_without_query_returns_all(): void
    {
        ScoutProduct::create(['name' => 'A', 'description' => 'X', 'price' => 1]);
        ScoutProduct::create(['name' => 'B', 'description' => 'Y', 'price' => 2]);

        $driver = new ScoutDriver();
        $builder = new SearchQueryBuilder(ScoutProduct::class, $driver);

        $results = $driver->search($builder);

        $this->assertCount(2, $results);
    }

    public function test_paginate_returns_paginator(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            ScoutProduct::create(['name' => "Product $i", 'description' => 'Test', 'price' => $i * 100]);
        }

        $driver = new ScoutDriver();
        $builder = new SearchQueryBuilder(ScoutProduct::class, $driver);

        $paginator = $driver->paginate($builder, 3);

        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $paginator);
        $this->assertEquals(5, $paginator->total());
    }

    public function test_index_calls_searchable(): void
    {
        $product = \Mockery::mock(\SmartSearch\Tests\Stubs\ScoutProduct::class)->makePartial();
        $product->shouldReceive('searchable')->once();

        $driver = new ScoutDriver();
        $driver->index($product);
    }

    public function test_delete_calls_unsearchable(): void
    {
        $product = \Mockery::mock(\SmartSearch\Tests\Stubs\ScoutProduct::class)->makePartial();
        $product->shouldReceive('unsearchable')->once();

        $driver = new ScoutDriver();
        $driver->delete($product);
    }

    public function test_get_name(): void
    {
        $driver = new ScoutDriver();
        $this->assertEquals('scout', $driver->getName());
    }
}
