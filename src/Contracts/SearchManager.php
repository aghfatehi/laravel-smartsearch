<?php

namespace SmartSearch\Contracts;

use SmartSearch\Builders\SearchQueryBuilder;

interface SearchManager
{
    public function for(string $modelClass): SearchQueryBuilder;
    public function driver(?string $name = null): SearchDriver;
}
