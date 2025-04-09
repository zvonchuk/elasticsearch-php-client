# Metrics Aggregations

Metrics aggregations compute statistics over a set of values extracted from the documents.

## Average Aggregation

Calculates the average of a numeric field:

```php
<?php
use Zvonchuk\Elastic\Search\Aggregations\AggregationBuilders;

$avgAgg = AggregationBuilders::avg('avg_price')
    ->field('price');
```

This generates:

```json
{
  "avg_price": {
    "avg": {
      "field": "price"
    }
  }
}
```

## Stats Aggregation

Computes stats (min, max, sum, count, avg) over a numeric field:

```php
<?php
use Zvonchuk\Elastic\Search\Aggregations\AggregationBuilders;

$statsAgg = AggregationBuilders::stats('price_stats')
    ->field('price');
```

This generates:

```json
{
  "price_stats": {
    "stats": {
      "field": "price"
    }
  }
}
```

## Extended Stats Aggregation

Provides extended statistics (including standard deviation, variance, etc.):

```php
<?php
use Zvonchuk\Elastic\Search\Aggregations\AggregationBuilders;

$extStatsAgg = AggregationBuilders::extendedStats('price_extended_stats')
    ->field('price');
```

## Geo Centroid Aggregation

Calculates the weighted centroid from all geo points in the aggregation:

```php
<?php
use Zvonchuk\Elastic\Search\Aggregations\AggregationBuilders;

$geoCentroidAgg = AggregationBuilders::geoCentroid('center_of_locations')
    ->field('location');
```

## Using Metrics Aggregations in a Search

Here's a practical example of using metrics aggregations:

```php
<?php
use Zvonchuk\Elastic\Core\SearchRequest;
use Zvonchuk\Elastic\Search\Builder\SearchSourceBuilder;
use Zvonchuk\Elastic\Query\QueryBuilders;
use Zvonchuk\Elastic\Search\Aggregations\AggregationBuilders;

// Create a query for active products
$query = QueryBuilders::termQuery('status', 'active');

// Set up the search with multiple aggregations
$searchSource = new SearchSourceBuilder();
$searchSource->query($query);
$searchSource->size(0);  // Only interested in aggregations

// Add multiple statistics aggregations
$searchSource->aggregation(
    AggregationBuilders::avg('avg_price')->field('price')
);
$searchSource->aggregation(
    AggregationBuilders::stats('rating_stats')->field('rating')
);
$searchSource->aggregation(
    AggregationBuilders::extendedStats('extended_price_stats')->field('price')
);

// Execute the search
$request = new SearchRequest('products');
$request->source($searchSource);
$response = $client->search($request);

// Process the aggregation results
$aggs = $response->getAggregations();

// Process average price
$avgPrice = $aggs['avg_price']['value'];
echo "Average price: $avgPrice\n";

// Process rating stats
$ratingStats = $aggs['rating_stats'];
echo "Rating stats: Min {$ratingStats['min']}, Max {$ratingStats['max']}, Avg {$ratingStats['avg']}\n";

// Process extended price stats
$extendedStats = $aggs['extended_price_stats'];
echo "Price standard deviation: {$extendedStats['std_deviation']}\n";
```
