# elasticsearch-php-client

[![Latest Stable Version](https://poser.pugx.org/zvonchuk/elasticsearch-php-client/v/stable)](https://packagist.org/packages/zvonchuk/elasticsearch-php-client) [![Total Downloads](https://poser.pugx.org/zvonchuk/elasticsearch-php-client/downloads)](https://packagist.org/packages/zvonchuk/elasticsearch-php-client)

High-level client for Elasticsearch. Its goal is to provide common ground for all Elasticsearch-related code in PHP; because of this it tries to be opinion-free and very extendable.

## Features

- Simple, fluent query building API
- Support for all common Elasticsearch operations
- Simplified index management
- Aggregations with an intuitive builder pattern
- Built-in query types for geo, range, term, and text searches
- Flexible sorting options
- Bulk operation support

## Documentation

- [Getting Started](https://zvonchuk.github.io/elasticsearch-php-client/getting-started.html)
- [Client Setup](https://zvonchuk.github.io/elasticsearch-php-client/client-setup.html)
- [Queries](https://zvonchuk.github.io/elasticsearch-php-client/queries/)
- [Aggregations](https://zvonchuk.github.io/elasticsearch-php-client/aggregations/)
- [Document Operations](https://zvonchuk.github.io/elasticsearch-php-client/document-operations/)
- [Indices Management](https://zvonchuk.github.io/elasticsearch-php-client/indices/)
- [Sorting](https://zvonchuk.github.io/elasticsearch-php-client/sorting/)
- [Advanced Examples](https://zvonchuk.github.io/elasticsearch-php-client/examples/)

## Installation via Composer

```bash
composer require zvonchuk/elasticsearch-php-client
```

## PHP Version Requirement
Version 0.1 of this library requires at least PHP version 7.1.

elasticsearch-php-client | PHP Version
-- | --
0.1 |>= 7.1.0

## Quick Start Example
```php
<?php
require 'vendor/autoload.php';

use Zvonchuk\Elastic\Client;
use Zvonchuk\Elastic\Core\SearchRequest;
use Zvonchuk\Elastic\Search\Builder\SearchSourceBuilder;
use Zvonchuk\Elastic\Query\QueryBuilders;

// Connect to Elasticsearch
$client = Client::getInstance(['localhost:9200']);

// Create a search query
$searchSource = new SearchSourceBuilder();
$searchSource->query(
    QueryBuilders::matchQuery('title', 'elasticsearch')
        ->operator('AND')
);

// Execute the search
$request = new SearchRequest('my_index');
$request->source($searchSource);
$response = $client->search($request);

// Process results
$hits = $response->getHits();
foreach ($hits as $hit) {
    echo "Document ID: {$hit['_id']}, Title: {$hit['_source']['title']}\n";
}
```
