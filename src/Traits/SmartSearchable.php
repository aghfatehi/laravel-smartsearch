<?php

namespace SmartSearch\Traits;

use SmartSearch\Builders\SearchQueryBuilder;
use SmartSearch\Indexing\ModelObserver;

trait SmartSearchable
{
    public static function bootSmartSearchable(): void
    {
        static::created(fn ($model) => app(ModelObserver::class)->created($model));
        static::updated(fn ($model) => app(ModelObserver::class)->updated($model));
        static::deleted(fn ($model) => app(ModelObserver::class)->deleted($model));
    }

    public function getSmartSearchableFields(): array
    {
        return property_exists($this, 'smartSearchable') ? $this->smartSearchable : [];
    }

    public function searchableEmbeddings(): array
    {
        return $this->getSmartSearchableFields();
    }

    public function getSmartSearchIndexName(): string
    {
        $prefix = config('smartsearch.index_prefix', '');
        return $prefix . $this->getTable();
    }

    public static function search(string $query): SearchQueryBuilder
    {
        return app(\SmartSearch\Contracts\SmartSearchManager::class)
            ->for(static::class)
            ->query($query);
    }
}
