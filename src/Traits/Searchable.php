<?php

namespace SmartSearch\Traits;

use SmartSearch\Builders\SearchQueryBuilder;
use SmartSearch\Indexing\ModelObserver;

trait Searchable
{
    public static function bootSearchable(): void
    {
        static::created(fn ($model) => app(ModelObserver::class)->created($model));
        static::updated(fn ($model) => app(ModelObserver::class)->updated($model));
        static::deleted(fn ($model) => app(ModelObserver::class)->deleted($model));
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
