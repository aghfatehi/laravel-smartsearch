<?php

namespace SmartSearch;

use Illuminate\Support\Facades\Log;
use SmartSearch\Builders\SearchQueryBuilder;
use SmartSearch\Contracts\SearchDriver;
use SmartSearch\Contracts\SearchManager as SearchManagerContract;
use SmartSearch\Drivers\DatabaseDriver;
use SmartSearch\Drivers\ElasticsearchDriver;
use SmartSearch\Drivers\OpenSearchDriver;

class SearchManager implements SearchManagerContract
{
    private array $drivers = [];
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function for(string $modelClass): SearchQueryBuilder
    {
        $driver = $this->resolveDriver($this->config['driver']);
        $fallback = $this->resolveFallback();

        return new SearchQueryBuilder($modelClass, $driver, $fallback);
    }

    public function driver(?string $name = null): SearchDriver
    {
        return $this->resolveDriver($name ?? $this->config['driver']);
    }

    public function resolveDriver(string $name): SearchDriver
    {
        if (isset($this->drivers[$name])) {
            return $this->drivers[$name];
        }

        $driver = match ($name) {
            'elasticsearch' => $this->createElasticsearchDriver(),
            'opensearch' => $this->createOpenSearchDriver(),
            'database' => $this->createDatabaseDriver(),
            'scout' => $this->createScoutDriver(),
            default => throw new \InvalidArgumentException("Unknown search driver: {$name}"),
        };

        $this->drivers[$name] = $driver;
        return $driver;
    }

    private function resolveFallback(): ?SearchDriver
    {
        $fallbackName = $this->config['fallback'] ?? null;
        if (!$fallbackName || $fallbackName === $this->config['driver']) {
            return null;
        }
        return $this->resolveDriver($fallbackName);
    }

    private function createElasticsearchDriver(): ElasticsearchDriver
    {
        if (!class_exists(\Elastic\Elasticsearch\ClientBuilder::class)) {
            throw new \RuntimeException('Elasticsearch driver requires elasticsearch/elasticsearch package. Run: composer require elasticsearch/elasticsearch');
        }
        return new ElasticsearchDriver($this->config['elasticsearch'] ?? []);
    }

    private function createOpenSearchDriver(): OpenSearchDriver
    {
        if (!class_exists(\OpenSearch\ClientBuilder::class)) {
            throw new \RuntimeException('OpenSearch driver requires opensearch-project/opensearch-php package. Run: composer require opensearch-project/opensearch-php');
        }
        return new OpenSearchDriver($this->config['opensearch'] ?? []);
    }

    private function createDatabaseDriver(): DatabaseDriver
    {
        return new DatabaseDriver();
    }

    private function createScoutDriver(): SearchDriver
    {
        if (!class_exists(\Laravel\Scout\EngineManager::class)) {
            throw new \RuntimeException('Scout driver requires laravel/scout package. Run: composer require laravel/scout');
        }
        return new \SmartSearch\Drivers\ScoutDriver();
    }
}
