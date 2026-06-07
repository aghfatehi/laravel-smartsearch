<?php

namespace SmartSearch\Drivers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use SmartSearch\Builders\SearchQueryBuilder;
use SmartSearch\Contracts\EmbeddingProvider;
use SmartSearch\Contracts\SearchDriver;

class DatabaseDriver implements SearchDriver
{
    private ?string $dbDriver = null;
    private ?EmbeddingProvider $embeddingProvider;

    public function __construct(?EmbeddingProvider $embeddingProvider = null)
    {
        $this->embeddingProvider = $embeddingProvider;
    }

    public function search(SearchQueryBuilder $builder): Collection
    {
        $query = $this->buildBaseQuery($builder);

        $results = $query->get();

        if ($builder->similarTo !== null && $this->embeddingProvider) {
            $results = $this->applyVectorSearch($results, $builder);
        }

        return $results;
    }

    public function paginate(SearchQueryBuilder $builder, int $perPage): LengthAwarePaginator
    {
        $paginator = $this->buildBaseQuery($builder)->paginate($perPage);

        if ($builder->similarTo !== null && $this->embeddingProvider) {
            $sorted = $this->applyVectorSearch(collect($paginator->items()), $builder);
            $paginator->setCollection($sorted);
        }

        return $paginator;
    }

    public function index(Model $model): void
    {
        if (!$this->embeddingProvider) {
            return;
        }

        $embeddingText = $this->resolveEmbeddingText($model);
        if ($embeddingText === null) {
            return;
        }

        $vector = $this->embeddingProvider->embedText($embeddingText);
        $model->newQuery()
            ->where($model->getKeyName(), $model->getKey())
            ->update(['embedding' => json_encode($vector)]);
    }

    public function delete(Model $model): void {}

    public function bulkIndex(Collection $models): void
    {
        foreach ($models as $model) {
            $this->index($model);
        }
    }

    public function getName(): string
    {
        return 'database';
    }

    public function supportsVectorSearch(): bool
    {
        return $this->embeddingProvider !== null;
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

    private function applyVectorSearch(Collection $results, SearchQueryBuilder $builder): Collection
    {
        $queryVector = $this->embeddingProvider->embedText($builder->similarTo);
        $weight = $builder->hybridWeight;

        $scored = [];
        foreach ($results as $model) {
            $stored = $model->embedding ?? null;
            if (!$stored) {
                continue;
            }

            $storedVector = is_string($stored) ? json_decode($stored, true) : $stored;
            if (!is_array($storedVector)) {
                continue;
            }

            $similarity = $this->cosineSimilarity($queryVector, $storedVector);
            $scored[] = ['model' => $model, 'score' => $similarity];
        }

        usort($scored, fn ($a, $b) => $b['score'] <=> $a['score']);

        $sorted = new Collection();
        foreach ($scored as $item) {
            $sorted->push($item['model']);
        }

        if (count($scored) < $results->count()) {
            $seen = $sorted->pluck($sorted->first()->getKeyName() ?? 'id')->toArray();
            foreach ($results as $model) {
                $key = $model->getKey();
                if (!in_array($key, $seen)) {
                    $sorted->push($model);
                }
            }
        }

        return $sorted;
    }

    private function cosineSimilarity(array $a, array $b): float
    {
        $dot = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        $count = min(count($a), count($b));
        for ($i = 0; $i < $count; $i++) {
            $dot += $a[$i] * $b[$i];
            $normA += $a[$i] * $a[$i];
            $normB += $b[$i] * $b[$i];
        }

        $denom = sqrt($normA) * sqrt($normB);

        return $denom > 0 ? $dot / $denom : 0.0;
    }

    private function resolveEmbeddingText(Model $model): ?string
    {
        if (!method_exists($model, 'searchableEmbeddings')) {
            return null;
        }

        $fields = $model->searchableEmbeddings();
        if (empty($fields)) {
            return null;
        }

        $parts = [];
        foreach ($fields as $field) {
            $parts[] = (string) $model->{$field};
        }

        return implode(' ', $parts);
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
