# Sorting

Sorting allows you to order search results by specific fields or custom criteria.

## Available Sorting Options

- [Field Sort](field-sort.html) - Sort by specific fields
- [Geo Distance Sort](geo-sort.html) - Sort by distance from a location
- [Script Sort](script-sort.html) - Sort using custom scripts

## Sorting Principles

All sorting in Elasticsearch follows these principles:

1. Sort values are computed on a per-document basis
2. Multiple sorts can be combined (like ORDER BY in SQL)
3. By default, sorting is ascending (ASC), but can be set to descending (DESC)
4. Numeric sorting, string sorting, and geographic sorting are all supported

## Basic Sorting Structure

```php
<?php
use Zvonchuk\Elastic\Search\Builder\SearchSourceBuilder;
use Zvonchuk\Elastic\Search\Sort\SortBuilders;
use Zvonchuk\Elastic\Search\Sort\SortBuilder;

$searchSource = new SearchSourceBuilder();

// Add a sort (ascending by default)
$searchSource->sort(
    SortBuilders::fieldSort('price')
);

// Add a sort with explicit order
$searchSource->sort(
    SortBuilders::fieldSort('created_at')
        ->order(SortBuilder::DESC)
);
```

## Adding Multiple Sort Criteria

You can add multiple sort criteria, which will be applied in order:

```php
<?php
use Zvonchuk\Elastic\Search\Builder\SearchSourceBuilder;
use Zvonchuk\Elastic\Search\Sort\SortBuilders;
use Zvonchuk\Elastic\Search\Sort\SortBuilder;

$searchSource = new SearchSourceBuilder();

// First sort by category (ascending)
$searchSource->sort(
    SortBuilders::fieldSort('category')
);

// Then sort by price (ascending)
$searchSource->sort(
    SortBuilders::fieldSort('price')
);

// Then sort by name (descending)
$searchSource->sort(
    SortBuilders::fieldSort('name')
        ->order(SortBuilder::DESC)
);
```

## Example: Basic Search with Sorting

Here's a complete example of a search with sorting:

```php
<?php
use Zvonchuk\Elastic\Client;
use Zvonchuk\Elastic\Core\SearchRequest;
use Zvonchuk\Elastic\Search\Builder\SearchSourceBuilder;
use Zvonchuk\Elastic\Query\QueryBuilders;
use Zvonchuk\Elastic\Search\Sort\SortBuilders;
use Zvonchuk\Elastic\Search\Sort\SortBuilder;

$client = Client::getInstance(['localhost:9200']);

// Create a search query
$searchSource = new SearchSourceBuilder();
$searchSource->query(QueryBuilders::matchQuery('category', 'electronics'));

// Add sorting - first by price, then by rating
$searchSource->sort(SortBuilders::fieldSort('price')->order(SortBuilder::ASC));
$searchSource->sort(SortBuilders::fieldSort('rating')->order(SortBuilder::DESC));

// Execute the search
$request = new SearchRequest('products');
$request->source($searchSource);
$response = $client->search($request);

// Process results
foreach ($response->getHits() as $hit) {
    echo "Product: {$hit['_source']['name']}, ";
    echo "Price: \${$hit['_source']['price']}, ";
    echo "Rating: {$hit['_source']['rating']}\n";
}
```

Browse the sections to learn more about each sorting type.
