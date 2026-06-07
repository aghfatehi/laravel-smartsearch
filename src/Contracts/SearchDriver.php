<?php

namespace SmartSearch\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use SmartSearch\Builders\SearchQueryBuilder;

interface SearchDriver
{
    public function search(SearchQueryBuilder $builder): Collection;
    public function paginate(SearchQueryBuilder $builder, int $perPage): LengthAwarePaginator;
    public function index(Model $model): void;
    public function delete(Model $model): void;
    public function bulkIndex(Collection $models): void;
    public function getName(): string;
    public function supportsVectorSearch(): bool;
}
