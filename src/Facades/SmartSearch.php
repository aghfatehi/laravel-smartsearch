<?php

namespace SmartSearch\Facades;

use Illuminate\Support\Facades\Facade;

class SmartSearch extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \SmartSearch\Contracts\SmartSearchManager::class;
    }
}
