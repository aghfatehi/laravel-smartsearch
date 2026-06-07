<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Search Driver
    |--------------------------------------------------------------------------
    |
    | The primary search driver. Supported: "database", "elasticsearch", "scout"
    |
    | Env: SMARTSEARCH_DRIVER
    | Default: "database" (works immediately, no extra dependencies)
    |
    */
    'driver' => env('SMARTSEARCH_DRIVER', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Fallback Driver
    |--------------------------------------------------------------------------
    |
    | When the primary driver fails (e.g. Elasticsearch is down), the package
    | automatically falls back to this driver. Set to null to disable fallback.
    |
    | Env: SMARTSEARCH_FALLBACK
    | Default: "database"
    |
    */
    'fallback' => env('SMARTSEARCH_FALLBACK', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Queue Indexing
    |--------------------------------------------------------------------------
    |
    | If true, index/delete operations are dispatched to the queue (non-blocking).
    | If false, operations run synchronously in the same request.
    |
    | Env: SMARTSEARCH_QUEUE
    | Default: true
    |
    */
    'queue' => env('SMARTSEARCH_QUEUE', true),

    /*
    |--------------------------------------------------------------------------
    | Queue Connection
    |--------------------------------------------------------------------------
    |
    | The Laravel queue connection to use for index/delete jobs.
    | Uses the default connection if not specified.
    |
    | Env: SMARTSEARCH_CONNECTION
    | Default: null (uses default queue connection)
    |
    */
    'connection' => env('SMARTSEARCH_CONNECTION', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Elasticsearch Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your Elasticsearch cluster connection. Hosts are comma-separated.
    |
    */
    'elasticsearch' => [

        /*
        |--------------------------------------------------------------------------
        | Elasticsearch Hosts
        |--------------------------------------------------------------------------
        |
        | Comma-separated list of Elasticsearch node URLs.
        | Example: "host1:9200,host2:9200"
        |
        | Env: ELASTICSEARCH_HOSTS
        | Default: "localhost:9200"
        |
        */
        'hosts' => explode(',', env('ELASTICSEARCH_HOSTS', 'localhost:9200')),

        /*
        |--------------------------------------------------------------------------
        | Elasticsearch Analyzer
        |--------------------------------------------------------------------------
        |
        | The default text analyzer for Elasticsearch.
        | Common values: "standard", "arabic", "english", "french", etc.
        |
        | Env: ELASTICSEARCH_ANALYZER
        | Default: "standard"
        |
        */
        'analyzer' => env('ELASTICSEARCH_ANALYZER', 'standard'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Index Prefix
    |--------------------------------------------------------------------------
    |
    | Prefix added to all search index names. Useful for multi-tenant setups
    | or when sharing a single Elasticsearch cluster across environments.
    |
    | Env: SMARTSEARCH_INDEX_PREFIX
    | Default: "" (no prefix)
    |
    */
    'index_prefix' => env('SMARTSEARCH_INDEX_PREFIX', ''),
];
