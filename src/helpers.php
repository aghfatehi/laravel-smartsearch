<?php

use SmartSearch\Facades\Search;

if (!function_exists('smartSearch')) {
    function smartSearch(string $modelClass, ?string $query = null)
    {
        $builder = Search::for($modelClass);

        if ($query !== null) {
            $builder->query($query);
        }

        return $builder;
    }
}
