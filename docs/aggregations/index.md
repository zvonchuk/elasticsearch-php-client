# Aggregations

Aggregations allow you to generate analytics over your data. The elasticsearch-php-client provides a fluent API for building all types of Elasticsearch aggregations.

## Available Aggregation Types

- [Metrics Aggregations](metrics.html) - Calculate metrics like average, sum, etc.
- [Bucket Aggregations](bucket.html) - Group documents into buckets
- [Nested Aggregations](nested.html) - Combine aggregations for advanced analytics

## Aggregation Builder Pattern

All aggregation types follow a consistent builder pattern:

```php
<?php
use Zvonchuk\Elastic\Search\Aggregations\AggregationBuilders;

// Creating an aggregation
$avgAgg = AggregationBuilders::avg('average_price')
    ->field('price');
```

## Using Aggregations in a Search

Here's how to use aggregations in a search:

```php
<?php
use Zvonchuk\Elastic\Core\SearchRequest;
use Zvonchuk\Elastic\Search\Builder\SearchSourceBuilder;
use Zvonchuk\Elastic\Query\QueryBuilders;
use Zvonchuk\Elastic\Search\Aggregations\AggregationBuilders;

// Create a search source with query and aggregation
$searchSource = new SearchSourceBuilder();
$searchSource->query(QueryBuilders::matchAllQuery());
$searchSource->size(0);  // No hits, only aggregations

// Add an average price aggregation
$avgAgg = AggregationBuilders::avg('average_price')
    ->field('price');
$searchSource->aggregation($avgAgg);

// Execute the search
$request = new SearchRequest('products');
$request->source($searchSource);
$response = $client->search($request);

// Process the aggregation results
$aggs = $response->getAggregations();
$averagePrice = $aggs['average_price']['value'];
echo "Average price: $averagePrice\n";
```

Browse the sections to learn more about each aggregation type.
