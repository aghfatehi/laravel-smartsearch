<?php

namespace SmartSearch\Tests\Unit;

use SmartSearch\Builders\SearchQueryBuilder;
use SmartSearch\Drivers\DatabaseDriver;
use SmartSearch\Drivers\OpenSearchDriver;
use SmartSearch\Tests\Stubs\Product;
use SmartSearch\Tests\TestCase;

class OpenSearchDriverTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpProductTable();

        Product::create(['name' => 'iPhone 15', 'description' => 'Apple phone', 'price' => 5000]);
        Product::create(['name' => 'Samsung Galaxy', 'description' => 'Android phone', 'price' => 4000]);
    }

    public function test_search_returns_matching_models_from_opensearch_ids(): void
    {
        $response = $this->mockSearchResponse(['1']);
        $client = \Mockery::mock();
        $client->shouldReceive('search')->once()->andReturn($response);

        $driver = new OpenSearchDriver(['hosts' => ['localhost:9200']], $client);
        $builder = new SearchQueryBuilder(Product::class, new DatabaseDriver());
        $builder->query('iPhone');

        $results = $driver->search($builder);

        $this->assertCount(1, $results);
        $this->assertEquals('iPhone 15', $results->first()->name);
    }

    public function test_search_returns_empty_when_no_ids(): void
    {
        $response = $this->mockSearchResponse([]);
        $client = \Mockery::mock();
        $client->shouldReceive('search')->once()->andReturn($response);

        $driver = new OpenSearchDriver(['hosts' => ['localhost:9200']], $client);
        $builder = new SearchQueryBuilder(Product::class, new DatabaseDriver());
        $builder->query('iPhone');

        $results = $driver->search($builder);

        $this->assertCount(0, $results);
    }

    public function test_index_creates_index_and_document(): void
    {
        $existsResponse = \Mockery::mock();
        $existsResponse->shouldReceive('asBool')->once()->andReturn(false);

        $indices = \Mockery::mock();
        $indices->shouldReceive('exists')->once()->andReturn($existsResponse);
        $indices->shouldReceive('create')->once();
        $indices->shouldReceive('refresh')->once();

        $client = \Mockery::mock();
        $client->shouldReceive('indices')->andReturn($indices);
        $client->shouldReceive('index')->once();

        $driver = new OpenSearchDriver(['hosts' => ['localhost:9200']], $client);
        $driver->index(Product::first());
    }

    public function test_delete_removes_document(): void
    {
        $client = \Mockery::mock();
        $client->shouldReceive('delete')->once();

        $driver = new OpenSearchDriver(['hosts' => ['localhost:9200']], $client);
        $driver->delete(Product::first());
    }

    public function test_delete_ignores_404(): void
    {
        $client = \Mockery::mock();
        $client->shouldReceive('delete')->once()->andThrow(
            new \RuntimeException('not found', 404)
        );

        $driver = new OpenSearchDriver(['hosts' => ['localhost:9200']], $client);
        $driver->delete(Product::first());
    }

    public function test_get_name(): void
    {
        $client = \Mockery::mock();
        $driver = new OpenSearchDriver(['hosts' => ['localhost:9200']], $client);
        $this->assertEquals('opensearch', $driver->getName());
    }

    private function mockSearchResponse(array $ids): array
    {
        $hits = [];
        foreach ($ids as $id) {
            $hits[] = ['_id' => (string) $id, '_source' => []];
        }

        return ['hits' => ['hits' => $hits]];
    }
}
