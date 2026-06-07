<p align="center">
 <a href="https://www.php.net/"><img src="https://img.shields.io/badge/php-^8.1-8892BF.svg?style=for-the-badge&logo=php" alt="PHP Version"></a>
 <a href="https://laravel.com/"><img src="https://img.shields.io/badge/Laravel-9|10|11|12|13-FF2D20.svg?style=for-the-badge&logo=laravel" alt="Laravel Version"></a>
 <a href="#driver-system"><img src="https://img.shields.io/badge/Drivers-Elasticsearch_%2B_Scout_%2B_Database-00A859.svg?style=for-the-badge" alt="Drivers"></a>
 <a href="LICENSE"><img src="https://img.shields.io/badge/license-MIT-blue.svg?style=for-the-badge" alt="License"></a>
 <a href="https://github.com/aghfatehi/laravel-smartsearch/actions"><img src="https://img.shields.io/github/actions/workflow/status/aghfatehi/laravel-smartsearch/tests.yml?style=for-the-badge&label=Tests&branch=main" alt="Tests"></a>
 <a href="https://packagist.org/packages/aghfatehi/laravel-smartsearch"><img src="https://img.shields.io/packagist/v/aghfatehi/laravel-smartsearch.svg?style=for-the-badge" alt="Packagist"></a>
 <a href="https://packagist.org/packages/aghfatehi/laravel-smartsearch"><img src="https://img.shields.io/packagist/dt/aghfatehi/laravel-smartsearch.svg?style=for-the-badge" alt="Downloads"></a>
 <a href="https://fsoftdev.com"><img src="https://img.shields.io/badge/FsoftDev-FsoftDev.com-blue.svg?style=for-the-badge" alt="FsoftDev"></a>
 <a href="https://github.com/aghfatehi"><img src="https://img.shields.io/badge/Author-AL--AGHBARI%20Fatehi-blue.svg?style=for-the-badge" alt="Author"></a>
</p>

<h1 align="center">Laravel SmartSearch</h1>
<p align="center">
    <strong>Laravel search package</strong> — the most versatile <strong>full text search for Laravel</strong> with Elasticsearch, Scout, and database search.<br>
    Zero-config, production-safe, <strong>queue-driven search indexing</strong>. A powerful <strong>Eloquent search</strong> engine for your models.
</p>

---

## What is Laravel SmartSearch?

**Laravel SmartSearch** is a powerful, production-ready **Laravel search package** that provides a unified search API across **Elasticsearch**, **Laravel Scout**, and **database full-text search** — a true **Laravel Scout alternative** with more flexibility. Built by **Fatehi AL-AGHBARI**, it solves the common pain points teams face when adding search to Laravel applications — complex setup, vendor lock-in, and brittle fallback logic.

Whether you need **Elasticsearch for Laravel**, a **Laravel Scout alternative**, or **database search for Laravel**, SmartSearch gives you one clean **Laravel search API** that works everywhere. The **Eloquent search** layer integrates directly with your models for zero-friction indexing.

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
- **Laravel Scout alternative** — drop-in compatible, works with Algolia, MeiliSearch, Typesense
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

| Feature | Elasticsearch Native | Laravel Scout Native | SmartSearch |
|---------|----------------|-----------------|-------------|
| **Single API across engines** | No — own query DSL only | No — Scout providers only (Algolia, MeiliSearch, Typesense) | Yes — works with database, Elasticsearch **and** Scout providers through one unified API |
| **Standalone DB driver** | No | Requires Scout ecosystem | Yes — works as a standalone database driver without installing Scout or any other package |
| **Queue auto-indexing** | No — you build it | Opt-in (`SCOUT_QUEUE=true` + config) | Yes — **enabled by default**, zero config, auto-dispatches queue jobs |
| **Automatic fallback** | No — downtime = 503 | No — only pagination count fallback | Yes — configurable fallback driver when primary is unreachable |
| **Arabic normalization** | Server-side only (index analyzer config) | No | Yes — PHP-level normalization (أ/إ/آ → ا, ة → ه, ى → ي) applied consistently across **all** drivers |
| **First-class ES support** | N/A (is Elasticsearch) | Requires community driver (`laravel-scout-elastic`) | Yes — built-in Elasticsearch driver with full ClientBuilder config, no extra packages needed |

SmartSearch is **not** "yet another way to call Elasticsearch or Scout." It is an **abstraction layer** that:
- Unifies database, Elasticsearch, **and** Scout under one fluent API
- Adds features neither engine provides alone (PHP-level Arabic normalization, driver fallback, queue-by-default indexing)
- Gives Elasticsearch first-class support without community adapters
- Lets you start searching in under a minute — then switch or upgrade engines later by changing one `.env` line

---

```bash
composer require aghfatehi/laravel-smartsearch
```

Publish the config (optional):

```bash
php artisan vendor:publish --tag=smartsearch-config
```

> **Optional drivers**: To use **Elasticsearch for Laravel**, run `composer require elasticsearch/elasticsearch`. To use **Laravel Scout**, run `composer require laravel/scout`. The package handles both gracefully without breaking.

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

> **Note:** The Elasticsearch client is bundled with the package — no extra `composer require` needed. Just set the environment variables above and it works.

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
| DatabaseDriver (default - works OOB)        |
| ElasticsearchDriver (optional high-perf)    |
| ScoutDriver (optional - Algolia, Meili...)  |
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
