<?php

namespace SmartSearch\Drivers;

use Elastic\Elasticsearch\ClientBuilder;
use Elastic\Elasticsearch\ClientInterface;
use Elastic\Elasticsearch\Exception\ElasticsearchException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use SmartSearch\Builders\SearchQueryBuilder;
use SmartSearch\Contracts\EmbeddingProvider;
use SmartSearch\Contracts\SearchDriver;
use SmartSearch\Indexing\IndexMapper;

class ElasticsearchDriver implements SearchDriver
{
    private ClientInterface $client;
    private IndexMapper $mapper;
    private string $analyzer;
    private ?EmbeddingProvider $embeddingProvider;

    public function __construct(array $config = [], ?ClientInterface $client = null, ?EmbeddingProvider $embeddingProvider = null)
    {
        $this->analyzer = $config['analyzer'] ?? 'standard';
        $this->embeddingProvider = $embeddingProvider;

        if ($client) {
            $this->client = $client;
        } else {
            $builder = ClientBuilder::create();

            if (!empty($config['cloud_id'])) {
                $builder->setElasticCloudId($config['cloud_id']);
            } else {
                $hosts = $config['hosts'] ?? ['localhost:9200'];
                $builder->setHosts($hosts);
            }

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
        } catch (ElasticsearchException $e) {
            Log::error('SmartSearch Elasticsearch search failed', [
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
        $countParams['size'] = 0;
        $countResponse = $this->client->count(['index' => $countParams['index']]);
        $total = $countResponse['count'] ?? 0;

        $params = $this->buildSearchParams($builder, $model);
        $params['size'] = $perPage;
        $params['from'] = ($page - 1) * $perPage;

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

        if ($this->embeddingProvider) {
            $embeddingText = $this->resolveEmbeddingText($model);
            if ($embeddingText !== null) {
                $payload['body']['embedding'] = $this->embeddingProvider->embedText($embeddingText);
            }
        }

        try {
            $this->ensureIndexExists($model);
            $this->client->index($payload);
            $this->client->indices()->refresh(['index' => $payload['index']]);
        } catch (ElasticsearchException $e) {
            Log::error('SmartSearch Elasticsearch index failed', [
                'model' => get_class($model),
                'id' => $model->getKey(),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
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

    public function delete(Model $model): void
    {
        $payload = $this->mapper->deletePayload($model);

        try {
            $this->client->delete($payload);
        } catch (ElasticsearchException $e) {
            if ($e->getCode() !== 404) {
                Log::error('SmartSearch Elasticsearch delete failed', [
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
        } catch (ElasticsearchException $e) {
            Log::error('SmartSearch Elasticsearch bulk index failed', [
                'count' => $models->count(),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function getName(): string
    {
        return 'elasticsearch';
    }

    public function supportsVectorSearch(): bool
    {
        return $this->embeddingProvider !== null;
    }

    private function buildSearchParams(SearchQueryBuilder $builder, Model $model): array
    {
        $fields = $model->getSmartSearchableFields();
        $indexName = $model->getSmartSearchIndexName();

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

        if ($builder->similarTo !== null) {
            $this->assertVectorSearchEnabled();
            $queryVector = $this->embeddingProvider->embedText($builder->similarTo);
            $body['knn'] = [
                'field' => 'embedding',
                'query_vector' => $queryVector,
                'k' => ($builder->limit ?? 10) * 2,
                'num_candidates' => 100,
            ];
        }

        $params = [
            'index' => $indexName,
            'body' => $body,
        ];

        if ($builder->limit !== null) {
            $params['size'] = $builder->limit;
        }

        if ($builder->offset !== null) {
            $params['from'] = $builder->offset;
        }

        return $params;
    }

    private function assertVectorSearchEnabled(): void
    {
        if (!$this->embeddingProvider) {
            throw new \RuntimeException('Vector search requires SMARTSEARCH_EMBEDDINGS_ENABLED=true and a running embedding provider (Ollama).');
        }
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
        $indexName = $model->getSmartSearchIndexName();
        $exists = $this->client->indices()->exists(['index' => $indexName]);

        if (!$exists->asBool()) {
            $vectorDim = $this->embeddingProvider ? $this->embeddingProvider->dimensions() : null;
            $schema = $this->mapper->schema($model, $this->analyzer, $vectorDim);
            $this->client->indices()->create($schema);
        }
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
