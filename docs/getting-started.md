# Getting Started with Elasticsearch PHP Client

This guide will help you get started with the elasticsearch-php-client library.

## Installation

The library can be installed via Composer:

```bash
composer require zvonchuk/elasticsearch-php-client
```

## Requirements

- PHP 8.1 or higher
- Elasticsearch 7.x (the package uses the official `elasticsearch/elasticsearch` 7.x client)
- Composer for dependency management

## Basic Setup

First, require the Composer autoloader and initialize the client:

```php
<?php
require 'vendor/autoload.php';

use Zvonchuk\Elastic\Client;

// Connect to a single Elasticsearch node
$client = Client::create(['localhost:9200']);

// Connect to multiple nodes
$client = Client::create([
    'elasticsearch1:9200',
    'elasticsearch2:9200',
    'elasticsearch3:9200'
]);
```

## Your First Query

Let's perform a simple search query:

```php
<?php
use Zvonchuk\Elastic\Core\SearchRequest;
use Zvonchuk\Elastic\Search\Builder\SearchSourceBuilder;
use Zvonchuk\Elastic\Query\QueryBuilders;

// Create a search source with a match query
$searchSource = new SearchSourceBuilder();
$searchSource->query(
    QueryBuilders::matchQuery('content', 'elasticsearch')
);

// Create and execute the search request
$request = new SearchRequest('my_index');
$request->source($searchSource);
$response = $client->search($request);

// Process the search results
echo "Found {$response->getTotal()} documents\n";
foreach ($response->hits() as $hit) {
    echo "Document ID: {$hit->id}, Score: {$hit->score}\n";
    print_r($hit->source);
}
```

## Next Steps

- Learn about [different query types](queries/)
- Explore [aggregations](aggregations/)
- See how to [manage documents](document-operations/)
- Understand [index operations](indices/)
