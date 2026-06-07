<?php

return [
    'driver' => env('SMARTSEARCH_DRIVER', 'database'),
    'fallback' => env('SMARTSEARCH_FALLBACK', 'database'),
    'queue' => env('SMARTSEARCH_QUEUE', true),
    'connection' => env('SMARTSEARCH_CONNECTION', 'default'),
    'elasticsearch' => [
        'hosts' => explode(',', env('ELASTICSEARCH_HOSTS', 'localhost:9200')),
        'analyzer' => env('ELASTICSEARCH_ANALYZER', 'standard'),
    ],
    'index_prefix' => env('SMARTSEARCH_INDEX_PREFIX', ''),
];
