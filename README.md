<p align="center">
 <a href="https://www.php.net/"><img src="https://img.shields.io/badge/php-^8.1-8892BF.svg?style=for-the-badge&logo=php" alt="PHP Version"></a>
 <a href="https://laravel.com/"><img src="https://img.shields.io/badge/Laravel-9|10|11|12|13-FF2D20.svg?style=for-the-badge&logo=laravel" alt="Laravel Version"></a>
 <a href="#driver-system"><img src="https://img.shields.io/badge/Drivers-OpenSearch_%2B_Elasticsearch_%2B_Scout_%2B_Database-00A859.svg?style=for-the-badge" alt="Drivers"></a>
 <a href="LICENSE"><img src="https://img.shields.io/badge/license-MIT-blue.svg?style=for-the-badge" alt="License"></a>
 <a href="https://github.com/aghfatehi/laravel-smartsearch/actions"><img src="https://img.shields.io/github/actions/workflow/status/aghfatehi/laravel-smartsearch/tests.yml?style=for-the-badge&label=Tests&branch=main" alt="Tests"></a>
 <a href="https://packagist.org/packages/aghfatehi/laravel-smartsearch"><img src="https://img.shields.io/packagist/v/aghfatehi/laravel-smartsearch.svg?style=for-the-badge" alt="Packagist"></a>
 <a href="https://packagist.org/packages/aghfatehi/laravel-smartsearch"><img src="https://img.shields.io/packagist/dt/aghfatehi/laravel-smartsearch.svg?style=for-the-badge" alt="Downloads"></a>
 <a href="https://fsoftdev.com"><img src="https://img.shields.io/badge/FsoftDev-FsoftDev.com-blue.svg?style=for-the-badge" alt="FsoftDev"></a>
 <a href="https://github.com/aghfatehi"><img src="https://img.shields.io/badge/Author-AL--AGHBARI%20Fatehi-blue.svg?style=for-the-badge" alt="Author"></a>
</p>

<h1 align="center">Laravel SmartSearch</h1>
<p align="center">
    <strong>Laravel search package</strong> — the most versatile <strong>full text search for Laravel</strong> with OpenSearch, Elasticsearch, Scout, and database search.<br>
    Zero-config, production-safe, <strong>queue-driven search indexing</strong>. A powerful <strong>Eloquent search</strong> engine for your models.
</p>

---

## What is Laravel SmartSearch?

**Laravel SmartSearch** is a powerful, production-ready **Laravel search package** that provides a unified search API across **OpenSearch**, **Elasticsearch**, **Laravel Scout**, and **database full-text search** — a true **Laravel Scout alternative** with more flexibility. Built by **Fatehi AL-AGHBARI**, it solves the common pain points teams face when adding search to Laravel applications — complex setup, vendor lock-in, and brittle fallback logic.

Whether you need **OpenSearch for Laravel**, **Elasticsearch for Laravel**, a **Laravel Scout alternative**, or **database search for Laravel**, SmartSearch gives you one clean **Laravel search API** that works everywhere. The **Eloquent search** layer integrates directly with your models for zero-friction indexing.

---

## Why Laravel SmartSearch?

Integrating **search for Laravel applications** often means:
- Hard-coding Elasticsearch queries
- Breaking changes when switching providers
- No safety net when the search engine goes down
- Painful multilingual setup (especially Arabic)

**SmartSearch fixes all of this:**

| Problem | Traditional Approach | SmartSearch |
|---------|-------------------|-------------|
| Setup complexity | Hours of config | 1-minute setup |
| Vendor lock-in | Tied to one engine | Switch drivers anytime |
| No fallback | 503 errors | Auto database fallback |
| Arabic search | Custom hacks | Built-in normalizer |
| Indexing overhead | Manual sync | Auto queue-driven |

---

## Features

- **Unified Laravel search API** — `Search::for(Model::class)->query('term')->get()`
- **Full text search for Laravel** — works out of the box with database search, no extra packages
- **Eloquent search** — trait-based, integrates directly with your models
- **Database search Laravel** — `LIKE` / `ILIKE` auto-detects MySQL, PostgreSQL, SQLite, SQL Server
- **Elasticsearch for Laravel** — high-performance dedicated search, install only if needed
- **OpenSearch for Laravel** — open-source search engine, fully compatible API
- **Laravel Scout bridge** — works with Algolia, MeiliSearch, Typesense through Scout
- **Search indexing** — queue-driven on model `created`/`updated`/`deleted`
- **Automatic safe fallback** — if Elasticsearch/Scout is down, falls back to database
- **Arabic search for Laravel** — built-in normalization for Arabic content
- **Multilingual search** — configurable analyzers for any language
- **Driver-based architecture** — Strategy Pattern, extensible
- **Works with any database** — MySQL, PostgreSQL, SQLite, SQL Server, no breaking changes
- **Laravel 9 / 10 / 11 / 12 / 13** — full backward compatibility

---

## What Sets SmartSearch Apart

These features are **not** available in Elasticsearch or Laravel Scout alone. They are the unique value SmartSearch brings:

| Feature | Elasticsearch Native | OpenSearch Native | Laravel Scout Native | SmartSearch |
|---------|----------------|-----------------|-----------------|-------------|
| **Single API across engines** | No | No | No — Scout providers only | Yes — database, OpenSearch, Elasticsearch, **and** Scout through one API |
| **Standalone DB driver** | No | No | Requires Scout ecosystem | Yes — works standalone, no extra packages |
| **Queue auto-indexing** | No — you build it | No — you build it | Opt-in (`SCOUT_QUEUE=true`) | Yes — **enabled by default**, zero config |
| **Automatic fallback** | No — downtime = 503 | No — downtime = 503 | No — only pagination count fallback | Yes — configurable fallback driver |
| **Arabic normalization** | Server-side only | Server-side only | No | Yes — PHP-level across **all** drivers |
| **OpenSearch support** | No | N/A (is OpenSearch) | Requires community driver | Yes — built-in driver, no extra config |
| **Elasticsearch support** | N/A (is Elasticsearch) | No | Requires community driver | Yes — built-in driver |

SmartSearch is **not** "yet another wrapper." It is an **open-source abstraction layer** that:
- Unifies database, OpenSearch, Elasticsearch, **and** Scout providers under one fluent API
- Adds features no single engine provides alone (PHP-level Arabic normalization, driver fallback, queue-by-default indexing)
- Gives OpenSearch and Elasticsearch first-class support — no community adapters needed
- Lets you start with the free database driver, migrate to self-hosted OpenSearch, then scale to Elasticsearch or Scout providers — all by changing one `.env` line

---

## Driver Architecture

SmartSearch decouples your application from the search engine. The architecture has three layers — your code, the manager, and the drivers:

### Layer 1 — Your Code (unified API, never changes)

```php
Search::for(Product::class)->query('phone')->where('price', '>', 100)->get();
```

### Layer 2 — SearchManager (routes to the active driver)

| Setting | Value |
|---------|-------|
| `SMARTSEARCH_DRIVER` | Pick one: `database`, `opensearch`, `elasticsearch`, `scout` |
| `SMARTSEARCH_FALLBACK` | Optional fallback if primary driver fails |

### Layer 3 — Drivers (execution engines)

| Driver | Type | Mechanism | Cost | Setup Effort |
|--------|------|-----------|------|-------------|
| `DatabaseDriver` | Standalone | `LIKE` / `ILIKE` auto-detect | Free | None — works immediately |
| `OpenSearchDriver` | Standalone | HTTP API via `opensearch-php` | Free | Medium — Docker self-hosted |
| `ElasticsearchDriver` | Standalone | HTTP API via `elasticsearch-php` | Cloud / Self-hosted | Medium — Cloud ID or hosts |
| `ScoutDriver` | Bridge | Delegates to Scout providers | Per provider | Low — Scout + API keys |

ScoutDriver providers:

| Provider | Type | Environment |
|----------|------|-------------|
| Algolia | Paid cloud | SaaS, zero server management |
| MeiliSearch | Free open-source / Paid cloud | Self-hosted or managed cloud |
| Typesense | Free open-source / Paid cloud | Self-hosted or managed cloud |

---

## Installation

### Core package

```bash
composer require aghfatehi/laravel-smartsearch
```

This installs the core package with the **database driver** only — no extra dependencies, no conflicts.

### Optional driver packages

The `database` driver works immediately. For other search engines, install the corresponding package:

| Engine | Command | Required for |
|--------|---------|-------------|
| **Elasticsearch** | `composer require elasticsearch/elasticsearch` | `SMARTSEARCH_DRIVER=elasticsearch` |
| **OpenSearch** | `composer require opensearch-project/opensearch-php` | `SMARTSEARCH_DRIVER=opensearch` |
| **Scout** (Algolia / MeiliSearch / Typesense) | `composer require laravel/scout` | `SMARTSEARCH_DRIVER=scout` |

The package auto-detects which drivers are installed — missing packages just mean that driver throws a clear error message if used.

### Publish config (optional)

```bash
php artisan vendor:publish --tag=smartsearch-config
```

---

## Quick Setup

### 1. Add the trait to your model

```php
use SmartSearch\Traits\Searchable;

class Product extends Model
{
    use Searchable;

    protected $searchable = ['name', 'description', 'price'];
}
```

### 2. (Optional) Configure your driver

The default driver is `database` — works immediately with your existing database. To switch to Elasticsearch:

```env
SMARTSEARCH_DRIVER=elasticsearch
SMARTSEARCH_FALLBACK=database
```

That's it.

## Elasticsearch Setup

To use the **Elasticsearch for Laravel** driver, set the driver in your `.env`:

```env
SMARTSEARCH_DRIVER=elasticsearch
```

### Connecting to Elasticsearch

**Local / self-hosted:**

```env
ELASTICSEARCH_HOSTS=localhost:9200
```

**With Basic Auth:**

```env
ELASTICSEARCH_HOSTS=localhost:9200
ELASTICSEARCH_USER=elastic
ELASTICSEARCH_PASS=changeme
```

**With API Key (takes precedence over Basic Auth):**

```env
ELASTICSEARCH_HOSTS=localhost:9200
ELASTICSEARCH_API_KEY=base64encodedapikey
```

> Where to get an API key: https://cloud.elastic.co > Your deployment > Stack Management > Security > API Keys > Create API key

**Elastic Cloud:**

```env
SMARTSEARCH_DRIVER=elasticsearch
ELASTICSEARCH_CLOUD_ID=my-cluster:dXM...
ELASTICSEARCH_USER=elastic
ELASTICSEARCH_PASS=yourpassword
```

No port or host needed — Cloud ID resolves endpoints automatically.

> Where to get Cloud ID: https://cloud.elastic.co > Your deployment > Copy Cloud ID (or Help icon > Connection Details)

### Additional Options

```env
ELASTICSEARCH_RETRIES=3          # Connection retries on failure
ELASTICSEARCH_SSL_VERIFY=true    # SSL cert verification
ELASTICSEARCH_ANALYZER=arabic    # Text analyzer (standard, arabic, english, etc.)
```

> **Note:** The Elasticsearch driver requires a separate install: `composer require elasticsearch/elasticsearch`. The package detects its presence automatically.

---

## OpenSearch Setup

To use the **OpenSearch for Laravel** driver, first install the client:

```bash
composer require opensearch-project/opensearch-php
```

Then set the driver in your `.env`:

```env
SMARTSEARCH_DRIVER=opensearch
SMARTSEARCH_FALLBACK=database
```

### Connecting to OpenSearch

OpenSearch uses the same HTTP API as Elasticsearch. The driver requires `opensearch-project/opensearch-php` but the setup is almost identical:

**Local / self-hosted:**

```env
OPENSEARCH_HOSTS=https://localhost:9200
OPENSEARCH_USER=admin
OPENSEARCH_PASS=admin
OPENSEARCH_SSL_VERIFY=false    # Self-signed certs in dev
```

**With API Key:**

```env
OPENSEARCH_HOSTS=https://opensearch-cluster:9200
OPENSEARCH_API_KEY=base64encodedapikey
```

**Docker (OpenSearch official image):**

```yaml
# docker-compose.yml
services:
  opensearch:
    image: opensearchproject/opensearch:latest
    environment:
      - discovery.type=single-node
      - plugins.security.disabled=true    # Disable security for dev
    ports:
      - "9200:9200"
```

> **Note:** OpenSearch is the **open-source** fork of Elasticsearch — fully compatible API, Apache 2.0 license, no Elastic Cloud dependency. Perfect for self-hosted production search without licensing costs.

### Additional Options

```env
OPENSEARCH_RETRIES=3          # Connection retries on failure
OPENSEARCH_SSL_VERIFY=true    # SSL cert verification
OPENSEARCH_ANALYZER=arabic    # Text analyzer (standard, arabic, english, etc.)
```

---

## Scout Driver

To use the Scout driver, you first install and configure [Laravel Scout](https://laravel.com/docs/scout) normally — it has its own `.env` variables and `config/scout.php` settings independent of SmartSearch:

```env
SMARTSEARCH_DRIVER=scout         # SmartSearch uses Scout
SCOUT_DRIVER=algolia             # Scout's own driver (algolia / meilisearch / typesense / database / collection)
ALGOLIA_APP_ID=your-app-id
ALGOLIA_SECRET=your-write-api-key
```

Since Scout manages its own model events, **use Scout's own `Searchable` trait** on your model (not SmartSearch's):

```php
use Laravel\Scout\Searchable;

class Product extends Model
{
    use Searchable;
}
```

SmartSearch's `ScoutDriver` will call Scout's API through your model. Auto-indexing is handled by Scout internally.

| Service | Signup | Get API Keys |
|---------|--------|-------------|
| **Algolia** | https://www.algolia.com/users/sign_up | Dashboard > API Keys |
| **MeiliSearch Cloud** | https://cloud.meilisearch.com | Project Settings > API Keys |
| **Typesense Cloud** | https://cloud.typesense.org | Dashboard > API Keys |

> Scout is optional (`require-dev`). Run `composer require laravel/scout` and publish its config to use it.

---

### Environment Variables

| Variable | Default | Description |
|----------|---------|-------------|
| `SMARTSEARCH_DRIVER` | `database` | Primary search driver: `database`, `elasticsearch`, or `scout` |
| `SMARTSEARCH_FALLBACK` | `database` | Fallback driver when primary is unavailable |
| `SMARTSEARCH_QUEUE` | `true` | Enable/disable queue-driven indexing |
| `SMARTSEARCH_CONNECTION` | `default` | Queue connection for index/delete jobs |
| `SMARTSEARCH_INDEX_PREFIX` | `` | Prefix for all search index names |
| `ELASTICSEARCH_HOSTS` | `localhost:9200` | Comma-separated Elasticsearch hosts |
| `ELASTICSEARCH_ANALYZER` | `standard` | Elasticsearch text analyzer (e.g. `arabic`, `english`) |

---

## Usage

### Basic search

```php
use SmartSearch\Facades\Search;

$results = Search::for(Product::class)
    ->query('iphone')
    ->get();
```

### Search with filters

```php
$results = Search::for(Product::class)
    ->query('iphone')
    ->where('price', '<', 5000)
    ->where('brand', 'Apple')
    ->get();
```

### Paginated search

```php
$results = Search::for(Product::class)
    ->query('laptop')
    ->paginate(20);
```

### Eloquent-style shortcut

```php
Product::search('iphone')->get();
```

### Helper function

```php
smartSearch(Product::class, 'iphone')->get();
```

---

## Auto Indexing

SmartSearch hooks into Eloquent events to keep your search index in sync automatically:

| Event | Action |
|-------|--------|
| `created` | Queue `IndexDocument` job |
| `updated` | Queue `IndexDocument` job |
| `deleted` | Queue `DeleteDocument` job |

All operations are **queue-based** (non-blocking) and configurable.

---

## Safe Database Fallback

When your primary search engine (Elasticsearch / Scout) is unavailable:

```php
// No special error handling needed
$results = Search::for(Product::class)->query('iphone')->get();
// Automatically falls back to database search
```

The database driver auto-detects your database engine:

| Database | LIKE Operator | Case-Insensitive |
|----------|--------------|-----------------|
| MySQL | `LIKE` | Depends on collation |
| PostgreSQL | `ILIKE` | Always |
| SQLite | `LIKE` | ASCII |
| SQL Server | `LIKE` | Depends on collation |

---

## Arabic & Multilingual Search

Full **Arabic search for Laravel** and **multilingual search** support out of the box:

```php
use SmartSearch\Support\ArabicNormalizer;

ArabicNormalizer::normalize('مُحَمَّد'); // محمد
ArabicNormalizer::normalize('أحمد إبراهيم آدم'); // احمد ابراهيم ادم
```

Configurable Elasticsearch analyzer for any language:

```php
// config/smartsearch.php
'elasticsearch' => [
    'analyzer' => 'arabic', // or 'standard', 'english', custom, etc.
],
```

---

## Driver System

| Driver | When to use |
|--------|------------|
| **DatabaseDriver** (default) | Works out of the box — no extra packages needed |
| **ElasticsearchDriver** | High-performance, dedicated search infrastructure |
| **ScoutDriver** | Already using Laravel Scout (Algolia, MeiliSearch, Typesense) |

### Configuration

```php
// config/smartsearch.php
return [
    'driver' => env('SMARTSEARCH_DRIVER', 'elasticsearch'),
    'fallback' => env('SMARTSEARCH_FALLBACK', 'database'),
    'queue' => env('SMARTSEARCH_QUEUE', true),
    'connection' => env('SMARTSEARCH_CONNECTION', 'default'),
    'elasticsearch' => [
        'hosts' => explode(',', env('ELASTICSEARCH_HOSTS', 'localhost:9200')),
        'analyzer' => env('ELASTICSEARCH_ANALYZER', 'standard'),
    ],
    'index_prefix' => env('SMARTSEARCH_INDEX_PREFIX', ''),
];
```

---

## Architecture

Built on **clean architecture** principles:

- **Strategy Pattern** for drivers
- **Service Container** binding
- **Event-driven** indexing
- **SOLID** principles throughout

```
Laravel Application
    |
    v
Laravel SmartSearch Layer
    |
    v
+---------------------------------------------+
| DatabaseDriver    (default - works OOB)     |
| OpenSearchDriver  (optional - self-hosted)  |
| ElasticsearchDriver (optional - high-perf)  |
| ScoutDriver       (optional - Algolia, ...) |
+---------------------------------------------+
| Automatic safe fallback between drivers     |
+---------------------------------------------+
```

---

## Use Cases

- **E-commerce search** — product search with filters and faceting
- **ERP systems** — search across invoices, customers, orders
- **SaaS platforms** — multi-tenant search (coming soon)
- **Real estate listings** — full-text property search
- **Document management** — search titles, descriptions, content
- **Arabic content platforms** — normalized Arabic search for Laravel

---

## Requirements

- **PHP** 8.1+
- **Laravel** 9.x / 10.x / 11.x / 12.x / 13.x
- **Database** MySQL / PostgreSQL / SQLite / SQL Server (any Laravel-supported database)
- **Elasticsearch** 7.x+ (only if using the Elasticsearch driver — optional)

---

## Contributing

1. Fork the repo
2. Create a feature branch (`git checkout -b feature/my-feature`)
3. Commit your changes (`git commit -m 'Add my feature'`)
4. Push (`git push origin feature/my-feature`)
5. Open a Pull Request

---

## Support

If this **Laravel search package** helps you:
- Star the repository on GitHub
- Report issues
- Share with the Laravel community
- Looking for an **Elasticsearch Laravel** or **database search** solution? This is it.

---

## License

MIT License. Free for personal and commercial use.
