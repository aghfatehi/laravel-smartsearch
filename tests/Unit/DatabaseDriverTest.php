<?php

namespace SmartSearch\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use SmartSearch\Builders\SearchQueryBuilder;
use SmartSearch\Drivers\DatabaseDriver;
use SmartSearch\Tests\Stubs\Product;
use SmartSearch\Tests\TestCase;

class DatabaseDriverTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpProductTable();
    }

    public function test_search_returns_matching_models(): void
    {
        Product::create(['name' => 'iPhone 15', 'description' => 'Apple phone', 'price' => 5000]);
        Product::create(['name' => 'Samsung Galaxy', 'description' => 'Android phone', 'price' => 4000]);
        Product::create(['name' => 'MacBook Pro', 'description' => 'Apple laptop', 'price' => 10000]);

        $driver = new DatabaseDriver();
        $builder = new SearchQueryBuilder(Product::class, $driver);
        $builder->query('iPhone');

        $results = $driver->search($builder);

        $this->assertCount(1, $results);
        $this->assertEquals('iPhone 15', $results->first()->name);
    }

    public function test_search_with_where_filter(): void
    {
        Product::create(['name' => 'iPhone 15', 'description' => 'Phone', 'price' => 5000]);
        Product::create(['name' => 'iPhone 14', 'description' => 'Phone', 'price' => 3000]);

        $driver = new DatabaseDriver();
        $builder = new SearchQueryBuilder(Product::class, $driver);
        $builder->query('iPhone')->where('price', '>=', 4000);

        $results = $driver->search($builder);

        $this->assertCount(1, $results);
        $this->assertEquals('iPhone 15', $results->first()->name);
    }

    public function test_search_with_order_and_limit(): void
    {
        Product::create(['name' => 'A Phone', 'description' => 'Cheap', 'price' => 100]);
        Product::create(['name' => 'B Phone', 'description' => 'Mid', 'price' => 200]);
        Product::create(['name' => 'C Phone', 'description' => 'Expensive', 'price' => 300]);

        $driver = new DatabaseDriver();
        $builder = new SearchQueryBuilder(Product::class, $driver);
        $builder->query('Phone')->orderBy('price', 'desc')->limit(2);

        $results = $driver->search($builder);

        $this->assertCount(2, $results);
        $this->assertEquals('C Phone', $results[0]->name);
        $this->assertEquals('B Phone', $results[1]->name);
    }

    public function test_search_empty_query_returns_all(): void
    {
        Product::create(['name' => 'A', 'description' => 'X', 'price' => 1]);
        Product::create(['name' => 'B', 'description' => 'Y', 'price' => 2]);

        $driver = new DatabaseDriver();
        $builder = new SearchQueryBuilder(Product::class, $driver);

        $results = $driver->search($builder);

        $this->assertCount(2, $results);
    }

    public function test_paginate_returns_paginator(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            Product::create(['name' => "Product $i", 'description' => 'Test', 'price' => $i * 100]);
        }

        $driver = new DatabaseDriver();
        $builder = new SearchQueryBuilder(Product::class, $driver);

        $paginator = $driver->paginate($builder, 5);

        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $paginator);
        $this->assertEquals(10, $paginator->total());
        $this->assertCount(5, $paginator->items());
    }
}
