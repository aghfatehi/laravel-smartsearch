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
    <strong>The most versatile Laravel search package</strong> - Elasticsearch, Scout, or database fallback.<br>
    Zero-config, production-safe, queue-driven indexing.
</p>

---

## What is Laravel SmartSearch?

**Laravel SmartSearch** is a powerful, production-ready **Laravel search package** that provides a unified search API across **Elasticsearch**, **Laravel Scout**, and **database full-text search**. Built by **Fatehi AL-AGHBARI**, it solves the common pain points teams face when adding search to Laravel applications - complex setup, vendor lock-in, and brittle fallback logic.

Whether you need **Elasticsearch Laravel integration**, a **Laravel Scout alternative**, or a **database-driven search fallback**, SmartSearch gives you one clean API that works everywhere.

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

- **Unified Laravel search API** - `Search::for(Model::class)->query('term')->get()`
- **Works out of the box** - no extra packages required, database driver is default
- **Database search** - `LIKE` / `ILIKE` (auto-detects MySQL, PostgreSQL, SQLite, SQL Server)
- **Elasticsearch driver** - optional, install only if needed
- **Laravel Scout driver** - optional, drop-in compatible
- **Auto indexing** - queue-driven on model `created`/`updated`/`deleted`
- **Automatic safe fallback** - if Elasticsearch/Scout is down, falls back to database
- **Arabic & multilingual support** - normalization, configurable analyzers
- **Driver-based architecture** - Strategy Pattern, extensible
- **Works with any database** - MySQL, PostgreSQL, SQLite, SQL Server - no breaking changes
- **Laravel 9 / 10 / 11 / 12 / 13** - full backward compatibility

---

## Installation

**No extra dependencies required.** The package works immediately with database search.

```bash
composer require aghfatehi/laravel-smartsearch
```

Publish the config (optional):

```bash
php artisan vendor:publish --tag=smartsearch-config
```

> **Optional drivers**: To use Elasticsearch, run `composer require elasticsearch/elasticsearch`. To use Scout, run `composer require laravel/scout`. The package handles both gracefully without breaking.

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

Built-in **Arabic text normalization** for search indexing:

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

- **E-commerce search** - product search with filters and faceting
- **ERP systems** - search across invoices, customers, orders
- **SaaS platforms** - multi-tenant search (coming soon)
- **Real estate listings** - full-text property search
- **Document management** - search titles, descriptions, content
- **Arabic content platforms** - normalized Arabic search

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

If this Laravel search package helps you:
- Star the repository on GitHub
- Report issues
- Share with the Laravel community

---

## License

MIT License. Free for personal and commercial use.

---

<p align="center">
 <a href="https://packagist.org/packages/aghfatehi/laravel-smartsearch"><img src="https://img.shields.io/badge/Packagist-aghfatehi%2Flaravel--smartsearch-blue.svg?style=for-the-badge" alt="Packagist"></a>
 <a href="https://fsoftdev.com"><img src="https://img.shields.io/badge/FsoftDev-FsoftDev.com-blue.svg?style=for-the-badge" alt="FsoftDev"></a>
 <a href="https://github.com/aghfatehi"><img src="https://img.shields.io/badge/Author-AL--AGHBARI%20Fatehi-blue.svg?style=for-the-badge" alt="Author"></a>
</p>
