# Nested Aggregations

Nested aggregations allow you to combine multiple aggregations to perform complex analytics operations.

## Adding Sub-Aggregations

Most bucket aggregations can have sub-aggregations:

```php
<?php
use Zvonchuk\Elastic\Search\Aggregations\AggregationBuilders;

// Create a terms aggregation
$termsAgg = AggregationBuilders::terms('categories')
    ->field('category.keyword')
    ->size(10);

// Add a sub-aggregation to calculate average price per category
$avgPriceAgg = AggregationBuilders::avg('avg_price')
    ->field('price');

// Add the sub-aggregation to the terms aggregation
$termsAgg->subAggregation($avgPriceAgg);
```

This generates:

```json
{
  "categories": {
    "terms": {
      "field": "category.keyword",
      "size": 10
    },
    "aggregations": {
      "avg_price": {
        "avg": {
          "field": "price"
        }
      }
    }
  }
}
```

## Filter Aggregation with Sub-Aggregations

Filter aggregations can filter documents before applying sub-aggregations:

```php
<?php
use Zvonchuk\Elastic\Search\Aggregations\AggregationBuilders;
use Zvonchuk\Elastic\Query\QueryBuilders;

// Create a filter for expensive products
$expensiveFilter = QueryBuilders::rangeQuery('price')->gte('100');

// Create a filter aggregation with the filter
$filterAgg = AggregationBuilders::filter('expensive_products', $expensiveFilter);

// Add sub-aggregations to the filter
$filterAgg->subAggregation(
    AggregationBuilders::terms('categories')->field('category.keyword')
);
$filterAgg->subAggregation(
    AggregationBuilders::avg('avg_price')->field('price')
);
```

## Complex Example: Multi-Level Nested Aggregations

Here's a more complex example with multiple levels of nesting:

```php
<?php
use Zvonchuk\Elastic\Core\SearchRequest;
use Zvonchuk\Elastic\Search\Builder\SearchSourceBuilder;
use Zvonchuk\Elastic\Query\QueryBuilders;
use Zvonchuk\Elastic\Search\Aggregations\AggregationBuilders;
use Zvonchuk\Elastic\Search\Aggregations\Bucket\DateHistogramBuilder;

$searchSource = new SearchSourceBuilder();
$searchSource->query(QueryBuilders::matchAllQuery());
$searchSource->size(0);

// First level: Group by date
$dateHistogram = AggregationBuilders::dateHistogram('sales_by_date')
    ->field('order_date')
    ->calendarInterval(DateHistogramBuilder::MONTH);

// Second level: Group by category within each date bucket
$categoryTerms = AggregationBuilders::terms('sales_by_category')
    ->field('category.keyword')
    ->size(10);

// Third level: Add statistics about the prices in each category
$categoryTerms->subAggregation(
    AggregationBuilders::stats('price_stats')->field('price')
);

// Add the category terms aggregation to the date histogram
$dateHistogram->subAggregation($categoryTerms);

// Add the date histogram to the search source
$searchSource->aggregation($dateHistogram);

// Execute the search
$request = new SearchRequest('orders');
$request->source($searchSource);
$response = $client->search($request);

// Process the multi-level aggregation results
$aggs = $response->getAggregations();

foreach ($aggs['sales_by_date']['buckets'] as $dateBucket) {
    $month = date('Y-m', $dateBucket['key'] / 1000);
    echo "Month: $month\n";
    
    foreach ($dateBucket['sales_by_category']['buckets'] as $categoryBucket) {
        $category = $categoryBucket['key'];
        $count = $categoryBucket['doc_count'];
        $avgPrice = $categoryBucket['price_stats']['avg'];
        
        echo "  - Category: $category, Orders: $count, Avg Price: $avgPrice\n";
    }
    echo "\n";
}
```

## Common Use Cases for Nested Aggregations

1. **Date histogram with stats**: Track metrics over time
2. **Terms with averages**: Compare performance across categories
3. **Geographic aggregations with metrics**: Analyze metrics by location
4. **Filtered data with detailed breakdowns**: Focus on specific segments
