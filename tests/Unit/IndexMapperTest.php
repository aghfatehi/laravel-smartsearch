<?php

namespace SmartSearch\Tests\Unit;

use SmartSearch\Indexing\IndexMapper;
use SmartSearch\Tests\Stubs\Product;
use SmartSearch\Tests\TestCase;

class IndexMapperTest extends TestCase
{
    private IndexMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mapper = new IndexMapper();
    }

    public function test_document_returns_correct_payload(): void
    {
        $product = new Product(['name' => 'iPhone', 'description' => 'Phone']);
        $product->id = 1;

        $payload = $this->mapper->document($product);

        $this->assertEquals('products', $payload['index']);
        $this->assertEquals('1', $payload['id']);
        $this->assertEquals('iPhone', $payload['body']['name']);
    }

    public function test_delete_payload_returns_correct_structure(): void
    {
        $product = new Product();
        $product->id = 5;

        $payload = $this->mapper->deletePayload($product);

        $this->assertEquals('products', $payload['index']);
        $this->assertEquals('5', $payload['id']);
    }

    public function test_schema_returns_correct_mapping(): void
    {
        $product = new Product();
        $schema = $this->mapper->schema($product);

        $this->assertEquals('products', $schema['index']);
        $this->assertArrayHasKey('mappings', $schema['body']);
        $this->assertArrayHasKey('properties', $schema['body']['mappings']);
    }
}
