<?php

namespace SmartSearch\Builders;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use SmartSearch\Contracts\SearchDriver;

class SearchQueryBuilder
{
    public string $modelClass;
    public ?string $query = null;
    public array $wheres = [];
    public array $orders = [];
    public ?int $limit = null;
    public ?int $offset = null;
    public ?string $similarTo = null;
    public float $hybridWeight = 0.5;

    private SearchDriver $driver;
    private ?SearchDriver $fallbackDriver;

    public function __construct(string $modelClass, SearchDriver $driver, ?SearchDriver $fallback = null)
    {
        $this->modelClass = $modelClass;
        $this->driver = $driver;
        $this->fallbackDriver = $fallback;
    }

    public function query(?string $query): self
    {
        $this->query = $query;
        return $this;
    }

    public function similarTo(string $text, float $weight = 0.5): self
    {
        $this->similarTo = $text;
        $this->hybridWeight = max(0.0, min(1.0, $weight));
        return $this;
    }

    public function where(string $field, string $operator, $value = null): self
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        $this->wheres[] = compact('field', 'operator', 'value');
        return $this;
    }

    public function orderBy(string $field, string $direction = 'asc'): self
    {
        $this->orders[] = compact('field', 'direction');
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    public function get(): Collection
    {
        try {
            return $this->driver->search($this);
        } catch (\Throwable $e) {
            return $this->handleFallback('search', $e, func_get_args());
        }
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        try {
            return $this->driver->paginate($this, $perPage);
        } catch (\Throwable $e) {
            return $this->handleFallback('paginate', $e, [$perPage]);
        }
    }

    private function handleFallback(string $method, \Throwable $e, array $args)
    {
        if (!$this->fallbackDriver) {
            throw $e;
        }

        \Illuminate\Support\Facades\Log::warning('SmartSearch: primary driver failed, using fallback', [
            'driver' => $this->driver->getName(),
            'fallback' => $this->fallbackDriver->getName(),
            'error' => $e->getMessage(),
        ]);

        return $this->fallbackDriver->{$method}($this, ...$args);
    }
}
