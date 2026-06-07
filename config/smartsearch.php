<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Search Driver
    |--------------------------------------------------------------------------
    |
    | The primary search driver. Supported: "database", "opensearch", "elasticsearch", "scout"
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
    /*
    |--------------------------------------------------------------------------
    | Elasticsearch Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your Elasticsearch cluster. At minimum you need to set the
    | host(s). Authentication can be done via Basic Auth (ELASTICSEARCH_USER /
    | ELASTICSEARCH_PASS) or API Key (ELASTICSEARCH_API_KEY).
    |
    | For Elastic Cloud, set ELASTICSEARCH_CLOUD_ID instead of hosts.
    |
    | Example .env:
    |   ELASTICSEARCH_HOSTS=localhost:9200
    |   ELASTICSEARCH_USER=elastic
    |   ELASTICSEARCH_PASS=changeme
    |   ELASTICSEARCH_API_KEY=base64key
    |   ELASTICSEARCH_CLOUD_ID=my-cluster:dXM...=
    |   ELASTICSEARCH_RETRIES=3
    |
    */
    'opensearch' => [
        /*
        |--------------------------------------------------------------------------
        | OpenSearch Hosts
        |--------------------------------------------------------------------------
        |
        | Comma-separated list of OpenSearch node URLs.
        | Example: "https://localhost:9200,https://opensearch-node2:9200"
        |
        | Env: OPENSEARCH_HOSTS
        | Default: "localhost:9200"
        |
        */
        'hosts' => explode(',', env('OPENSEARCH_HOSTS', 'localhost:9200')),

        /*
        |--------------------------------------------------------------------------
        | OpenSearch Analyzer
        |--------------------------------------------------------------------------
        |
        | The default text analyzer for OpenSearch.
        | Common values: "standard", "arabic", "english", "french", etc.
        |
        | Env: OPENSEARCH_ANALYZER
        | Default: "standard"
        |
        */
        'analyzer' => env('OPENSEARCH_ANALYZER', 'standard'),

        /*
        |--------------------------------------------------------------------------
        | Basic Authentication
        |--------------------------------------------------------------------------
        |
        | Username and password for OpenSearch Basic Auth.
        | Leave null if using API Key authentication instead.
        |
        | Env: OPENSEARCH_USER, OPENSEARCH_PASS
        | Default: null
        |
        */
        'user' => env('OPENSEARCH_USER'),
        'pass' => env('OPENSEARCH_PASS'),

        /*
        |--------------------------------------------------------------------------
        | API Key Authentication
        |--------------------------------------------------------------------------
        |
        | Base64-encoded API key for OpenSearch. Takes precedence over
        | Basic Auth if both are set.
        |
        | Env: OPENSEARCH_API_KEY
        | Default: null
        |
        */
        'api_key' => env('OPENSEARCH_API_KEY'),

        /*
        |--------------------------------------------------------------------------
        | Connection Retries
        |--------------------------------------------------------------------------
        |
        | Number of times to retry failed OpenSearch requests before giving up.
        |
        | Env: OPENSEARCH_RETRIES
        | Default: 3
        |
        */
        'retries' => env('OPENSEARCH_RETRIES', 3),

        /*
        |--------------------------------------------------------------------------
        | SSL Verification
        |--------------------------------------------------------------------------
        |
        | Enable or disable SSL certificate verification.
        | Set to false only in local/development environments (self-signed certs).
        |
        | Env: OPENSEARCH_SSL_VERIFY
        | Default: true
        |
        */
        'ssl_verify' => env('OPENSEARCH_SSL_VERIFY', true),
    ],

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

        /*
        |--------------------------------------------------------------------------
        | Elastic Cloud ID
        |--------------------------------------------------------------------------
        |
        | If using Elastic Cloud (https://cloud.elastic.co), set this instead of
        | hosts. The client will resolve the correct endpoints automatically.
        |
        | How to get it:
        |   https://cloud.elastic.co > Deployment > Copy Cloud ID
        |   See: https://www.elastic.co/search-labs/tutorials/install-elasticsearch/
        |        find-cloud-id-create-api-keys
        |
        | Env: ELASTICSEARCH_CLOUD_ID
        | Default: null
        |
        */
        'cloud_id' => env('ELASTICSEARCH_CLOUD_ID'),

        /*
        |--------------------------------------------------------------------------
        | Basic Authentication
        |--------------------------------------------------------------------------
        |
        | Username and password for Elasticsearch Basic Auth.
        | Leave null if using API Key authentication instead.
        |
        | Env: ELASTICSEARCH_USER, ELASTICSEARCH_PASS
        | Default: null
        |
        */
        'user' => env('ELASTICSEARCH_USER'),
        'pass' => env('ELASTICSEARCH_PASS'),

        /*
        |--------------------------------------------------------------------------
        | API Key Authentication
        |--------------------------------------------------------------------------
        |
        | Base64-encoded API key for Elasticsearch. Takes precedence over
        | Basic Auth if both are set.
        |
        | How to create:
        |   https://cloud.elastic.co > Your deployment > Stack Management >
        |   Security > API Keys > Create API key
        |   See: https://www.elastic.co/search-labs/tutorials/install-elasticsearch/
        |        find-cloud-id-create-api-keys
        |
        | Env: ELASTICSEARCH_API_KEY
        | Default: null
        |
        */
        'api_key' => env('ELASTICSEARCH_API_KEY'),

        /*
        |--------------------------------------------------------------------------
        | Connection Retries
        |--------------------------------------------------------------------------
        |
        | Number of times to retry failed Elasticsearch requests before giving up.
        |
        | Env: ELASTICSEARCH_RETRIES
        | Default: 3
        |
        */
        'retries' => env('ELASTICSEARCH_RETRIES', 3),

        /*
        |--------------------------------------------------------------------------
        | SSL Verification
        |--------------------------------------------------------------------------
        |
        | Enable or disable SSL certificate verification.
        | Set to false only in local/development environments.
        |
        | Env: ELASTICSEARCH_SSL_VERIFY
        | Default: true
        |
        */
        'ssl_verify' => env('ELASTICSEARCH_SSL_VERIFY', true),
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
    /*
    |--------------------------------------------------------------------------
    | Embeddings / Semantic Search
    |--------------------------------------------------------------------------
    |
    | When enabled, the package generates vector embeddings for model content
    | and supports similarTo() semantic search. Requires an embedding provider.
    |
    | Env: SMARTSEARCH_EMBEDDINGS_ENABLED
    | Default: false
    |
    */
    'embeddings' => [
        /*
        |--------------------------------------------------------------------------
        | Enable Embeddings
        |--------------------------------------------------------------------------
        |
        | Set to true to enable vector embeddings and semantic search.
        | When false (default), similarTo() throws a clear exception.
        | Environment variable: SMARTSEARCH_EMBEDDINGS_ENABLED
        |
        */
        'enabled' => env('SMARTSEARCH_EMBEDDINGS_ENABLED', false),

        /*
        |--------------------------------------------------------------------------
        | Embedding Provider
        |--------------------------------------------------------------------------
        |
        | The provider to use for generating embeddings.
        | Supported: "ollama"
        |
        */
        'provider' => env('SMARTSEARCH_EMBEDDINGS_PROVIDER', 'ollama'),

        /*
        |--------------------------------------------------------------------------
        | Embedding Model
        |--------------------------------------------------------------------------
        |
        | The model name used by the provider.
        | For Ollama: "nomic-embed-text", "all-minilm", "mxbai-embed-large", etc.
        | See available models: https://ollama.com/search?c=embedding
        |
        | Env: SMARTSEARCH_EMBEDDINGS_MODEL
        | Default: "nomic-embed-text"
        |
        */
        'model' => env('SMARTSEARCH_EMBEDDINGS_MODEL', 'nomic-embed-text'),

        /*
        |--------------------------------------------------------------------------
        | Ollama Host
        |--------------------------------------------------------------------------
        |
        | The URL of your Ollama server.
        | - Local install: http://localhost:11434
        | - Docker: http://ollama:11434 (if service name is "ollama")
        | - Remote: http://your-server:11434
        |
        | Env: SMARTSEARCH_EMBEDDINGS_HOST
        | Default: "http://localhost:11434"
        |
        */
        'host' => env('SMARTSEARCH_EMBEDDINGS_HOST', 'http://localhost:11434'),

        /*
        |--------------------------------------------------------------------------
        | Embedding Dimensions
        |--------------------------------------------------------------------------
        |
        | Vector dimensions for the model in use. If not set, the provider
        | auto-detects by running a test embedding on first call.
        | Common values: 768 (nomic-embed-text), 384 (all-minilm), 1024 (mxbai-embed-large)
        |
        | Env: SMARTSEARCH_EMBEDDINGS_DIMENSIONS
        | Default: null (auto-detect)
        |
        */
        'dimensions' => env('SMARTSEARCH_EMBEDDINGS_DIMENSIONS'),

        /*
        |--------------------------------------------------------------------------
        | Request Timeout
        |--------------------------------------------------------------------------
        |
        | Timeout in seconds for embedding API requests.
        |
        | Env: SMARTSEARCH_EMBEDDINGS_TIMEOUT
        | Default: 30
        |
        */
        'timeout' => env('SMARTSEARCH_EMBEDDINGS_TIMEOUT', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Index Prefix
    |--------------------------------------------------------------------------
    |
    | Prefix added to all search index names. Useful for multi-tenant setups
    | or when sharing a single search cluster across environments.
    |
    | Env: SMARTSEARCH_INDEX_PREFIX
    | Default: "" (no prefix)
    |
    */
    'index_prefix' => env('SMARTSEARCH_INDEX_PREFIX', ''),
];
