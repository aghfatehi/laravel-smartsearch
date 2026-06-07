<?php

namespace SmartSearch\Tests\Unit;

use SmartSearch\Builders\SearchQueryBuilder;
use SmartSearch\Contracts\SearchDriver;
use SmartSearch\Drivers\DatabaseDriver;
use SmartSearch\Tests\TestCase;

class SearchQueryBuilderTest extends TestCase
{
    private SearchQueryBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpProductTable();
        $this->builder = new SearchQueryBuilder(
            \SmartSearch\Tests\Stubs\Product::class,
            new DatabaseDriver()
        );
    }

    public function test_query_method(): void
    {
        $result = $this->builder->query('iphone');
        $this->assertSame($this->builder, $result);
        $this->assertEquals('iphone', $this->builder->query);
    }

    public function test_where_with_two_args(): void
    {
        $this->builder->where('price', 100);
        $this->assertCount(1, $this->builder->wheres);
        $this->assertEquals('price', $this->builder->wheres[0]['field']);
        $this->assertEquals('=', $this->builder->wheres[0]['operator']);
        $this->assertEquals(100, $this->builder->wheres[0]['value']);
    }

    public function test_where_with_three_args(): void
    {
        $this->builder->where('price', '<', 5000);
        $this->assertEquals('price', $this->builder->wheres[0]['field']);
        $this->assertEquals('<', $this->builder->wheres[0]['operator']);
        $this->assertEquals(5000, $this->builder->wheres[0]['value']);
    }

    public function test_order_by(): void
    {
        $this->builder->orderBy('price', 'desc');
        $this->assertCount(1, $this->builder->orders);
        $this->assertEquals('price', $this->builder->orders[0]['field']);
        $this->assertEquals('desc', $this->builder->orders[0]['direction']);
    }

    public function test_limit_and_offset(): void
    {
        $this->builder->limit(10)->offset(20);
        $this->assertEquals(10, $this->builder->limit);
        $this->assertEquals(20, $this->builder->offset);
    }

    public function test_get_returns_collection(): void
    {
        $results = $this->builder->query('test')->get();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $results);
    }

    public function test_fallback_on_driver_failure(): void
    {
        $failing = $this->createMock(SearchDriver::class);
        $failing->method('search')->willThrowException(new \RuntimeException('fail'));
        $failing->method('getName')->willReturn('failing');

        $fallback = new DatabaseDriver();

        $builder = new SearchQueryBuilder(
            \SmartSearch\Tests\Stubs\Product::class,
            $failing,
            $fallback
        );

        $results = $builder->query('test')->get();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $results);
    }

    public function test_fallback_throws_when_no_fallback_available(): void
    {
        $this->expectException(\RuntimeException::class);

        $failing = $this->createMock(SearchDriver::class);
        $failing->method('search')->willThrowException(new \RuntimeException('fail'));
        $failing->method('getName')->willReturn('failing');

        $builder = new SearchQueryBuilder(
            \SmartSearch\Tests\Stubs\Product::class,
            $failing,
            null
        );

        $builder->query('test')->get();
    }
}
