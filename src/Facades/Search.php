<?php

namespace SmartSearch\Facades;

use Illuminate\Support\Facades\Facade;

class Search extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \SmartSearch\Contracts\SearchManager::class;
    }
}
