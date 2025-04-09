# Bucket Aggregations

Bucket aggregations group documents into buckets based on criteria like terms, date ranges, or numeric ranges.

## Terms Aggregation

Groups documents by field values:

```php
<?php
use Zvonchuk\Elastic\Search\Aggregations\AggregationBuilders;

$termsAgg = AggregationBuilders::terms('categories')
    ->field('category.keyword')
    ->size(10);  // Return top 10 categories
```

This generates:

```json
{
  "categories": {
    "terms": {
      "field": "category.keyword",
      "size": 10
    }
  }
}
```

## Date Histogram Aggregation

Groups documents by date intervals:

```php
<?php
use Zvonchuk\Elastic\Search\Aggregations\AggregationBuilders;
use Zvonchuk\Elastic\Search\Aggregations\Bucket\DateHistogramBuilder;

$dateHistogram = AggregationBuilders::dateHistogram('sales_per_month')
    ->field('created_at')
    ->calendarInterval(DateHistogramBuilder::MONTH)
    ->minDocCount(0);  // Include empty buckets
```

This generates:

```json
{
  "sales_per_month": {
    "date_histogram": {
      "field": "created_at",
      "calendar_interval": "1M",
      "min_doc_count": 0
    }
  }
}
```

## Histogram Aggregation

Groups numeric values into buckets of a specified interval:

```php
<?php
use Zvonchuk\Elastic\Search\Aggregations\AggregationBuilders;

$histogramAgg = AggregationBuilders::histogram('price_ranges')
    ->field('price')
    ->interval(50)    // 0-50, 50-100, 100-150, etc.
    ->minDocCount(1); // Only include buckets with at least one document
```

## Percentiles Aggregation

Calculates percentiles of a numeric field:

```php
<?php
use Zvonchuk\Elastic\Search\Aggregations\AggregationBuilders;

$percentilesAgg = AggregationBuilders::percentiles('response_percentiles')
    ->field('response_time')
    ->percents([50, 95, 99])  // Median, 95th percentile, 99th percentile
    ->compression(100)
    ->keyed(true);
```

## Geo Hash Grid Aggregation

Groups geo-points into buckets using geohash:

```php
<?php
use Zvonchuk\Elastic\Search\Aggregations\AggregationBuilders;

$geoHashGridAgg = AggregationBuilders::geoHashGrid('location_grid')
    ->field('location')
    ->precision(5);  // Geohash precision level
```

## Example: Using Bucket Aggregations

Here's a practical example of using bucket aggregations:

```php
<?php
use Zvonchuk\Elastic\Core\SearchRequest;
use Zvonchuk\Elastic\Search\Builder\SearchSourceBuilder;
use Zvonchuk\Elastic\Query\QueryBuilders;
use Zvonchuk\Elastic\Search\Aggregations\AggregationBuilders;
use Zvonchuk\Elastic\Search\Aggregations\Bucket\DateHistogramBuilder;

// Create a query for active orders
$query = QueryBuilders::termQuery('status', 'completed');

// Set up the search with multiple bucket aggregations
$searchSource = new SearchSourceBuilder();
$searchSource->query($query);
$searchSource->size(0);  // Only interested in aggregations

// Add terms aggregation for product categories
$searchSource->aggregation(
    AggregationBuilders::terms('top_categories')
        ->field('category.keyword')
        ->size(5)
);

// Add date histogram for orders over time
$searchSource->aggregation(
    AggregationBuilders::dateHistogram('orders_over_time')
        ->field('order_date')
        ->calendarInterval(DateHistogramBuilder::MONTH)
);

// Execute the search
$request = new SearchRequest('orders');
$request->source($searchSource);
$response = $client->search($request);

// Process the aggregation results
$aggs = $response->getAggregations();

// Process top categories
echo "Top Categories:\n";
foreach ($aggs['top_categories']['buckets'] as $bucket) {
    $category = $bucket['key'];
    $count = $bucket['doc_count'];
    echo "- $category: $count orders\n";
}

// Process orders over time
echo "\nOrders Over Time:\n";
foreach ($aggs['orders_over_time']['buckets'] as $bucket) {
    $month = date('Y-m', $bucket['key'] / 1000);
    $count = $bucket['doc_count'];
    echo "- $month: $count orders\n";
}
```
