<?php

namespace SmartSearch\Drivers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use SmartSearch\Builders\SearchQueryBuilder;
use SmartSearch\Contracts\SearchDriver;

class DatabaseDriver implements SearchDriver
{
    private ?string $dbDriver = null;

    public function search(SearchQueryBuilder $builder): Collection
    {
        $query = $this->buildBaseQuery($builder);
        return $query->get();
    }

    public function paginate(SearchQueryBuilder $builder, int $perPage): LengthAwarePaginator
    {
        return $this->buildBaseQuery($builder)->paginate($perPage);
    }

    public function index(Model $model): void {}

    public function delete(Model $model): void {}

    public function bulkIndex(Collection $models): void {}

    public function getName(): string
    {
        return 'database';
    }

    private function buildBaseQuery(SearchQueryBuilder $builder)
    {
        $modelClass = $builder->modelClass;
        $query = $modelClass::query();

        if ($builder->query) {
            $model = new $modelClass;
            $fields = $model->getSearchableFields();

            if (!empty($fields)) {
                $searchTerm = $builder->query;

                if (config('smartsearch.elasticsearch.analyzer') === 'standard') {
                    $searchTerm = \SmartSearch\Support\ArabicNormalizer::normalize($searchTerm);
                }

                $query->where(function ($q) use ($fields, $searchTerm) {
                    $likeOp = $this->likeOperator();
                    foreach ($fields as $field) {
                        $q->orWhereRaw("{$field} {$likeOp} ?", ['%' . $searchTerm . '%']);
                    }
                });
            }
        }

        foreach ($builder->wheres as $where) {
            if (strtoupper($where['operator']) === 'IN') {
                $query->whereIn($where['field'], (array) $where['value']);
            } else {
                $query->where($where['field'], $where['operator'], $where['value']);
            }
        }

        foreach ($builder->orders as $order) {
            $query->orderBy($order['field'], $order['direction']);
        }

        if ($builder->limit !== null) {
            $query->limit($builder->limit);
        }

        if ($builder->offset !== null) {
            $query->offset($builder->offset);
        }

        return $query;
    }

    private function likeOperator(): string
    {
        $driver = $this->resolveDbDriver();

        return $driver === 'pgsql' ? 'ILIKE' : 'LIKE';
    }

    private function resolveDbDriver(): string
    {
        if ($this->dbDriver !== null) {
            return $this->dbDriver;
        }

        try {
            $this->dbDriver = DB::connection()->getDriverName();
        } catch (\Throwable) {
            $this->dbDriver = 'mysql';
        }

        return $this->dbDriver;
    }
}
