# elasticsearch-php-client

[![Latest Stable Version](https://poser.pugx.org/zvonchuk/elasticsearch-php-client/v/stable)](https://packagist.org/packages/zvonchuk/elasticsearch-php-client) [![Total Downloads](https://poser.pugx.org/zvonchuk/elasticsearch-php-client/downloads)](https://packagist.org/packages/zvonchuk/elasticsearch-php-client)

High-level client for Elasticsearch. Its goal is to provide common ground for all Elasticsearch-related code in PHP; because of this it tries to be opinion-free and very extendable.

## Features

- Fluent, typed query building in the style of the Java High Level REST Client
- `bool`, `match`, `multi_match`, `term`, `terms`, `range`, `exists`, `prefix`, `wildcard`, `ids`, `fuzzy`,
  `dis_max`, `constant_score`, `nested`, geo queries — `boost()` and `queryName()` on every query
- Typed responses: `SearchHit` objects with matched queries, mapping hits to your own classes
- Multi search: several searches in one round trip, failures reported instead of passing for empty results
- Aggregations with sub-aggregations, sorting, document and bulk operations
- Index management with aliases and atomic alias swaps for reindexing without downtime
- Wraps an official `elasticsearch/elasticsearch` client you configure — no hidden connections

## Documentation

- [Getting Started](https://zvonchuk.github.io/elasticsearch-php-client/getting-started.html)
- [Client Setup](https://zvonchuk.github.io/elasticsearch-php-client/client-setup.html)
- [Searching and Responses](https://zvonchuk.github.io/elasticsearch-php-client/search/)
- [Queries](https://zvonchuk.github.io/elasticsearch-php-client/queries/)
- [Aggregations](https://zvonchuk.github.io/elasticsearch-php-client/aggregations/)
- [Document Operations](https://zvonchuk.github.io/elasticsearch-php-client/document-operations/)
- [Indices Management](https://zvonchuk.github.io/elasticsearch-php-client/indices/)
- [Sorting](https://zvonchuk.github.io/elasticsearch-php-client/sorting/)
- [Advanced Examples](https://zvonchuk.github.io/elasticsearch-php-client/examples/)
- [Changelog](CHANGELOG.md) · [Upgrading from 0.x](UPGRADE-1.0.md)

## Installation via Composer

```bash
composer require zvonchuk/elasticsearch-php-client
```

## Requirements

elasticsearch-php-client | PHP | Elasticsearch | Official client
-- | -- | -- | --
1.x | >= 8.1 | 7.x | `elasticsearch/elasticsearch` ^7.11
0.x | >= 7.4 | 7.x | `elasticsearch/elasticsearch` ^7.11

## Quick Start Example

```php
<?php
require 'vendor/autoload.php';

use Zvonchuk\Elastic\Client;
use Zvonchuk\Elastic\Core\SearchRequest;
use Zvonchuk\Elastic\Query\QueryBuilders;
use Zvonchuk\Elastic\Search\Builder\SearchSourceBuilder;

// Connect (or wrap an official client you configured: new Client($elasticsearch))
$client = Client::create(['localhost:9200']);

// Build the search
$query = QueryBuilders::boolQuery()
    ->must(QueryBuilders::matchQuery('title', 'elasticsearch')->operator('and')->queryName('title'))
    ->filter(QueryBuilders::termQuery('published', true));

$source = (new SearchSourceBuilder())->query($query)->size(20)->timeout('2s');
$response = $client->search((new SearchRequest('articles'))->source($source));

// Process results
foreach ($response->hits() as $hit) {
    echo "{$hit->id} ({$hit->score}): {$hit->source['title']}\n";
}
```

## Development

```bash
composer test                                            # unit tests
ELASTICSEARCH_URL=http://localhost:9200 composer test:integration
composer analyse                                         # PHPStan level 8
```
