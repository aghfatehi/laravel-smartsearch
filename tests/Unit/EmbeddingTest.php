<?php

namespace SmartSearch\Tests\Unit;

use SmartSearch\Builders\SearchQueryBuilder;
use SmartSearch\Contracts\EmbeddingProvider;
use SmartSearch\Drivers\DatabaseDriver;
use SmartSearch\Tests\Stubs\Product;
use SmartSearch\Tests\TestCase;

class EmbeddingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpProductTable();
    }

    public function test_query_builder_similar_to_sets_value(): void
    {
        $driver = new DatabaseDriver();
        $builder = new SearchQueryBuilder(Product::class, $driver);

        $builder->similarTo('something similar');

        $this->assertEquals('something similar', $builder->similarTo);
        $this->assertEquals(0.5, $builder->hybridWeight);
    }

    public function test_query_builder_similar_to_with_custom_weight(): void
    {
        $driver = new DatabaseDriver();
        $builder = new SearchQueryBuilder(Product::class, $driver);

        $builder->similarTo('test', 0.8);

        $this->assertEquals(0.8, $builder->hybridWeight);
    }

    public function test_query_builder_similar_to_clamps_weight(): void
    {
        $driver = new DatabaseDriver();
        $builder = new SearchQueryBuilder(Product::class, $driver);

        $builder->similarTo('test', -0.5);
        $this->assertEquals(0.0, $builder->hybridWeight);

        $builder->similarTo('test', 1.5);
        $this->assertEquals(1.0, $builder->hybridWeight);
    }

    public function test_searchable_embeddings_returns_searchable_fields_by_default(): void
    {
        $model = new Product();
        $this->assertEquals(['name', 'description'], $model->searchableEmbeddings());
    }

    public function test_database_driver_supports_vector_search_with_provider(): void
    {
        $provider = \Mockery::mock(EmbeddingProvider::class);
        $driver = new DatabaseDriver($provider);

        $this->assertTrue($driver->supportsVectorSearch());
    }

    public function test_database_driver_does_not_support_vector_search_without_provider(): void
    {
        $driver = new DatabaseDriver();

        $this->assertFalse($driver->supportsVectorSearch());
    }

    public function test_embeddings_disabled_by_default(): void
    {
        $this->assertFalse(config('smartsearch.embeddings.enabled', false));
    }

    public function test_ollama_embedding_provider_throws_on_failed_request(): void
    {
        $provider = new \SmartSearch\Embedding\OllamaEmbeddingProvider([
            'host' => 'http://localhost:1',
            'model' => 'nomic-embed-text',
            'timeout' => 1,
        ]);

        $this->expectException(\RuntimeException::class);

        $provider->embedText('test');
    }

    public function test_database_driver_sorts_by_vector_similarity(): void
    {
        Product::create(['name' => 'iPhone', 'description' => 'Apple phone', 'price' => 5000]);
        Product::create(['name' => 'Samsung', 'description' => 'Android phone', 'price' => 4000]);
        Product::create(['name' => 'Dell Laptop', 'description' => 'Windows computer', 'price' => 3000]);

        $provider = \Mockery::mock(EmbeddingProvider::class);

        $provider->shouldReceive('embedText')
            ->with('electronic devices')
            ->andReturn([1.0, 0.0, 0.0]);

        $driver = new DatabaseDriver($provider);

        $model = Product::find(1);
        $model->embedding = json_encode([0.9, 0.1, 0.0]);
        $model->save();

        $model2 = Product::find(2);
        $model2->embedding = json_encode([0.8, 0.2, 0.0]);
        $model2->save();

        $model3 = Product::find(3);
        $model3->embedding = json_encode([0.1, 0.9, 0.0]);
        $model3->save();

        $builder = new SearchQueryBuilder(Product::class, $driver);
        $builder->similarTo('electronic devices');

        $results = $driver->search($builder);

        $this->assertGreaterThanOrEqual(1, $results->count());
        $this->assertEquals(1, $results->first()->getKey());
    }

    public function test_database_driver_index_stores_embedding(): void
    {
        Product::create(['name' => 'Test', 'description' => 'Test desc', 'price' => 100]);

        $provider = \Mockery::mock(EmbeddingProvider::class);
        $provider->shouldReceive('embedText')
            ->with('Test Test desc')
            ->andReturn([0.1, 0.2, 0.3]);

        $driver = new DatabaseDriver($provider);
        $driver->index(Product::first());

        $product = Product::first();
        $this->assertNotNull($product->embedding);

        $decoded = json_decode($product->embedding, true);
        $this->assertEquals([0.1, 0.2, 0.3], $decoded);
    }

    public function test_elasticsearch_driver_supports_vector_search_with_provider(): void
    {
        if (!class_exists(\Elastic\Elasticsearch\ClientBuilder::class)) {
            $this->markTestSkipped('elasticsearch/elasticsearch not installed');
        }

        $client = \Mockery::mock(\Elastic\Elasticsearch\ClientInterface::class);
        $provider = \Mockery::mock(EmbeddingProvider::class);

        $driver = new \SmartSearch\Drivers\ElasticsearchDriver(['hosts' => ['localhost:9200']], $client, $provider);

        $this->assertTrue($driver->supportsVectorSearch());
    }

    public function test_elasticsearch_driver_does_not_support_vector_search_without_provider(): void
    {
        if (!class_exists(\Elastic\Elasticsearch\ClientBuilder::class)) {
            $this->markTestSkipped('elasticsearch/elasticsearch not installed');
        }

        $client = \Mockery::mock(\Elastic\Elasticsearch\ClientInterface::class);
        $driver = new \SmartSearch\Drivers\ElasticsearchDriver(['hosts' => ['localhost:9200']], $client);

        $this->assertFalse($driver->supportsVectorSearch());
    }

    public function test_opensearch_driver_supports_vector_search_with_provider(): void
    {
        $client = \Mockery::mock();
        $provider = \Mockery::mock(EmbeddingProvider::class);
        $driver = new \SmartSearch\Drivers\OpenSearchDriver(['hosts' => ['localhost:9200']], $client, $provider);

        $this->assertTrue($driver->supportsVectorSearch());
    }

    public function test_opensearch_driver_does_not_support_vector_search_without_provider(): void
    {
        $client = \Mockery::mock();
        $driver = new \SmartSearch\Drivers\OpenSearchDriver(['hosts' => ['localhost:9200']], $client);

        $this->assertFalse($driver->supportsVectorSearch());
    }

    public function test_scout_driver_never_supports_vector_search(): void
    {
        if (!class_exists(\Laravel\Scout\EngineManager::class)) {
            $this->markTestSkipped('laravel/scout not installed');
        }

        $driver = new \SmartSearch\Drivers\ScoutDriver();

        $this->assertFalse($driver->supportsVectorSearch());
    }
}
