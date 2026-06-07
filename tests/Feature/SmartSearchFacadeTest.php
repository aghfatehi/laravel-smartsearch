<?php

namespace SmartSearch\Tests\Feature;

use SmartSearch\Facades\Search;
use SmartSearch\Tests\Stubs\Product;
use SmartSearch\Tests\TestCase;

class SmartSearchFacadeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpProductTable();
    }

    public function test_facade_search_returns_results(): void
    {
        Product::create(['name' => 'iPhone 15', 'description' => 'Apple smartphone', 'price' => 5000]);

        $results = Search::for(Product::class)
            ->query('iPhone')
            ->get();

        $this->assertCount(1, $results);
        $this->assertEquals('iPhone 15', $results->first()->name);
    }

    public function test_facade_search_with_filters(): void
    {
        Product::create(['name' => 'iPhone 15', 'description' => 'Pro', 'price' => 5000]);
        Product::create(['name' => 'iPhone 14', 'description' => 'Standard', 'price' => 3000]);

        $results = Search::for(Product::class)
            ->query('iPhone')
            ->where('price', '>=', 4000)
            ->get();

        $this->assertCount(1, $results);
        $this->assertEquals('iPhone 15', $results->first()->name);
    }

    public function test_helper_function(): void
    {
        Product::create(['name' => 'MacBook', 'description' => 'Apple laptop', 'price' => 10000]);

        $results = smartSearch(Product::class, 'MacBook')->get();

        $this->assertCount(1, $results);
        $this->assertEquals('MacBook', $results->first()->name);
    }

    public function test_eloquent_style_search(): void
    {
        Product::create(['name' => 'iPad', 'description' => 'Apple tablet', 'price' => 3000]);

        $results = Product::search('iPad')->get();

        $this->assertCount(1, $results);
        $this->assertEquals('iPad', $results->first()->name);
    }
}
