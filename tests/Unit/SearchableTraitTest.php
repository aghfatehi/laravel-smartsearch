<?php

namespace SmartSearch\Tests\Unit;

use SmartSearch\Tests\Stubs\Product;
use SmartSearch\Tests\TestCase;

class SearchableTraitTest extends TestCase
{
    public function test_trait_provides_searchable_fields(): void
    {
        $product = new Product();
        $fields = $product->getSmartSearchableFields();

        $this->assertEquals(['name', 'description'], $fields);
    }

    public function test_trait_provides_index_name(): void
    {
        $product = new Product();
        $index = $product->getSmartSearchIndexName();

        $this->assertEquals('products', $index);
    }

    public function test_trait_provides_static_search_method(): void
    {
        $builder = Product::search('test');

        $this->assertInstanceOf(\SmartSearch\Builders\SearchQueryBuilder::class, $builder);
        $this->assertEquals('test', $builder->query);
    }

    public function test_searchable_fields_empty_when_not_defined(): void
    {
        $model = new class extends \Illuminate\Database\Eloquent\Model {
            use \SmartSearch\Traits\SmartSearchable;
        };

        $this->assertEquals([], $model->getSmartSearchableFields());
    }
}
