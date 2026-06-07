<?php

namespace SmartSearch\Traits;

use SmartSearch\Builders\SearchQueryBuilder;
use SmartSearch\Indexing\ModelObserver;

trait Searchable
{
    public static function bootSearchable(): void
    {
        static::observe(ModelObserver::class);
    }

    public function getSearchableFields(): array
    {
        return property_exists($this, 'searchable') ? $this->searchable : [];
    }

    public function getSearchIndexName(): string
    {
        $prefix = config('smartsearch.index_prefix', '');
        return $prefix . $this->getTable();
    }

    public static function search(string $query): SearchQueryBuilder
    {
        return app(\SmartSearch\Contracts\SearchManager::class)
            ->for(static::class)
            ->query($query);
    }
}
