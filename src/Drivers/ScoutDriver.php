<?php

namespace SmartSearch\Drivers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use SmartSearch\Builders\SearchQueryBuilder;
use SmartSearch\Contracts\SearchDriver;

class ScoutDriver implements SearchDriver
{
    public function search(SearchQueryBuilder $builder): Collection
    {
        $scoutBuilder = $this->buildScoutQuery($builder);
        return $scoutBuilder->get();
    }

    public function paginate(SearchQueryBuilder $builder, int $perPage): LengthAwarePaginator
    {
        $scoutBuilder = $this->buildScoutQuery($builder);
        return $scoutBuilder->paginate($perPage);
    }

    public function index(Model $model): void
    {
        $model->searchable();
    }

    public function delete(Model $model): void
    {
        $model->unsearchable();
    }

    public function bulkIndex(Collection $models): void
    {
        $models->first()?->searchableUsing()->update($models->first()?->searchableAs());
        $models->each->searchable();
    }

    public function getName(): string
    {
        return 'scout';
    }

    public function supportsVectorSearch(): bool
    {
        return false;
    }

    private function buildScoutQuery(SearchQueryBuilder $builder)
    {
        $modelClass = $builder->modelClass;
        $scoutBuilder = $modelClass::search($builder->query ?? '');

        foreach ($builder->wheres as $where) {
            if (strtoupper($where['operator']) === 'IN') {
                $scoutBuilder->whereIn($where['field'], (array) $where['value']);
            } else {
                $scoutBuilder->where($where['field'], $where['value']);
            }
        }

        if ($builder->limit !== null) {
            $scoutBuilder->take($builder->limit);
        }

        if ($builder->orders) {
            foreach ($builder->orders as $order) {
                $scoutBuilder->orderBy($order['field'], $order['direction']);
            }
        }

        return $scoutBuilder;
    }
}
