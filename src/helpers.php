<?php

use SmartSearch\Facades\SmartSearch;

if (!function_exists('smartSearch')) {
    function smartSearch(string $modelClass, ?string $query = null)
    {
        $builder = SmartSearch::for($modelClass);

        if ($query !== null) {
            $builder->query($query);
        }

        return $builder;
    }
}
