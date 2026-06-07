<?php

namespace SmartSearch\Contracts;

use SmartSearch\Builders\SearchQueryBuilder;

interface SmartSearchManager
{
    public function for(string $modelClass): SearchQueryBuilder;
    public function driver(?string $name = null): SearchDriver;
}
