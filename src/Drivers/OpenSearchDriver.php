<?php

namespace SmartSearch\Drivers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use SmartSearch\Builders\SearchQueryBuilder;
use SmartSearch\Contracts\SearchDriver;
use SmartSearch\Indexing\IndexMapper;

class OpenSearchDriver implements SearchDriver
{
    private $client;
    private IndexMapper $mapper;

    public function __construct(array $config = [], ?object $client = null)
    {
        if ($client) {
            $this->client = $client;
        } else {
            $builder = \OpenSearch\ClientBuilder::create();

            $hosts = $config['hosts'] ?? ['localhost:9200'];
            $builder->setHosts($hosts);

            if (!empty($config['api_key'])) {
                $builder->setApiKey($config['api_key']);
            } elseif (!empty($config['user']) && !empty($config['pass'])) {
                $builder->setBasicAuthentication($config['user'], $config['pass']);
            }

            if (isset($config['retries'])) {
                $builder->setRetries((int) $config['retries']);
            }

            if (isset($config['ssl_verify'])) {
                $builder->setSSLVerification((bool) $config['ssl_verify']);
            }

            $this->client = $builder->build();
        }

        $this->mapper = new IndexMapper();
    }

    public function search(SearchQueryBuilder $builder): Collection
    {
        $model = new $builder->modelClass;
        $params = $this->buildSearchParams($builder, $model);

        try {
            $response = $this->client->search($params);
        } catch (\Throwable $e) {
            Log::error('SmartSearch OpenSearch search failed', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }

        return $this->hydrateModels($builder->modelClass, $response);
    }

    public function paginate(SearchQueryBuilder $builder, int $perPage): LengthAwarePaginator
    {
        $model = new $builder->modelClass;
        $page = ($builder->offset ?? 0) > 0 ? (int) floor($builder->offset / $perPage) + 1 : 1;

        $countParams = $this->buildSearchParams($builder, $model);
        $countParams['body']['size'] = 0;
        $countResponse = $this->client->count(['index' => $countParams['index']]);
        $total = $countResponse['count'] ?? 0;

        $params = $this->buildSearchParams($builder, $model);
        $params['body']['size'] = $perPage;
        $params['body']['from'] = ($page - 1) * $perPage;

        $response = $this->client->search($params);
        $results = $this->hydrateModels($builder->modelClass, $response);

        return new LengthAwarePaginator(
            $results,
            $total,
            $perPage,
            $page,
            ['path' => request()->path(), 'query' => request()->query()]
        );
    }

    public function index(Model $model): void
    {
        $payload = $this->mapper->document($model);

        try {
            $this->ensureIndexExists($model);
            $this->client->index($payload);
            $this->client->indices()->refresh(['index' => $payload['index']]);
        } catch (\Throwable $e) {
            Log::error('SmartSearch OpenSearch index failed', [
                'model' => get_class($model),
                'id' => $model->getKey(),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function delete(Model $model): void
    {
        $payload = $this->mapper->deletePayload($model);

        try {
            $this->client->delete($payload);
        } catch (\Throwable $e) {
            if ($e->getCode() !== 404) {
                Log::error('SmartSearch OpenSearch delete failed', [
                    'model' => get_class($model),
                    'id' => $model->getKey(),
                    'error' => $e->getMessage(),
                ]);
                throw $e;
            }
        }
    }

    public function bulkIndex(Collection $models): void
    {
        $params = ['body' => []];

        foreach ($models as $model) {
            $payload = $this->mapper->document($model);
            $params['body'][] = [
                'index' => [
                    '_index' => $payload['index'],
                    '_id' => $payload['id'],
                ],
            ];
            $params['body'][] = $payload['body'];
        }

        try {
            $this->client->bulk($params);
        } catch (\Throwable $e) {
            Log::error('SmartSearch OpenSearch bulk index failed', [
                'count' => $models->count(),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function getName(): string
    {
        return 'opensearch';
    }

    private function buildSearchParams(SearchQueryBuilder $builder, Model $model): array
    {
        $fields = $model->getSearchableFields();
        $indexName = $model->getSearchIndexName();

        $body = [];

        if ($builder->query) {
            $body['query'] = [
                'multi_match' => [
                    'query' => $builder->query,
                    'fields' => $fields,
                ],
            ];
        } else {
            $body['query'] = ['match_all' => new \stdClass()];
        }

        if (!empty($builder->wheres)) {
            $body['query'] = [
                'bool' => [
                    'must' => $body['query'],
                    'filter' => $this->buildFilters($builder->wheres),
                ],
            ];
        }

        if (!empty($builder->orders)) {
            $body['sort'] = [];
            foreach ($builder->orders as $order) {
                $body['sort'][$order['field']] = ['order' => $order['direction']];
            }
        }

        $params = [
            'index' => $indexName,
            'body' => $body,
        ];

        if ($builder->limit !== null) {
            $params['body']['size'] = $builder->limit;
        }

        if ($builder->offset !== null) {
            $params['body']['from'] = $builder->offset;
        }

        return $params;
    }

    private function buildFilters(array $wheres): array
    {
        $filters = [];
        foreach ($wheres as $where) {
            $operator = strtoupper($where['operator']);

            if ($operator === 'IN') {
                $filters[] = ['terms' => [$where['field'] => (array) $where['value']]];
            } elseif ($operator === '<') {
                $filters[] = ['range' => [$where['field'] => ['lt' => $where['value']]]];
            } elseif ($operator === '<=') {
                $filters[] = ['range' => [$where['field'] => ['lte' => $where['value']]]];
            } elseif ($operator === '>') {
                $filters[] = ['range' => [$where['field'] => ['gt' => $where['value']]]];
            } elseif ($operator === '>=') {
                $filters[] = ['range' => [$where['field'] => ['gte' => $where['value']]]];
            } else {
                $filters[] = ['term' => [$where['field'] => $where['value']]];
            }
        }
        return $filters;
    }

    private function ensureIndexExists(Model $model): void
    {
        $indexName = $model->getSearchIndexName();
        $exists = $this->client->indices()->exists(['index' => $indexName]);

        if ($exists->asBool()) {
            return;
        }

        $schema = $this->mapper->schema($model);
        $this->client->indices()->create($schema);
    }

    private function hydrateModels(string $modelClass, $response): Collection
    {
        $ids = [];
        $hits = $response['hits']['hits'] ?? [];

        foreach ($hits as $hit) {
            $ids[] = $hit['_id'];
        }

        if (empty($ids)) {
            return new Collection();
        }

        $models = $modelClass::whereIn('id', $ids)->get();
        $keyed = $models->keyBy('id');

        $sorted = new Collection();
        foreach ($ids as $id) {
            if (isset($keyed[$id])) {
                $sorted->push($keyed[$id]);
            }
        }

        return $sorted;
    }
}
