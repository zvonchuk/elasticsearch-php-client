# Aggregations with Filters

This example demonstrates how to use filtered aggregations for analytics purposes. This is useful for dashboards and reporting.

## Sales Analytics Dashboard

This example creates a dashboard with various metrics, filtered by a date range:

```php
<?php
use Zvonchuk\Elastic\Client;
use Zvonchuk\Elastic\Core\SearchRequest;
use Zvonchuk\Elastic\Search\Builder\SearchSourceBuilder;
use Zvonchuk\Elastic\Query\QueryBuilders;
use Zvonchuk\Elastic\Search\Aggregations\AggregationBuilders;
use Zvonchuk\Elastic\Search\Aggregations\Bucket\DateHistogramBuilder;

$client = Client::getInstance(['localhost:9200']);

// Filter parameters
$startDate = '2023-01-01';
$endDate = '2023-12-31';
$productCategory = 'electronics'; // Optional category filter

// Build the main filter query
$boolQuery = QueryBuilders::boolQuery();

// Date range filter
$boolQuery->filter(
    QueryBuilders::rangeQuery('order_date')
        ->gte($startDate)
        ->lte($endDate)
);

// Optional category filter
if (!empty($productCategory)) {
    $boolQuery->filter(
        QueryBuilders::termQuery('category', $productCategory)
    );
}

// Set up search with no hits, only aggregations
$searchSource = new SearchSourceBuilder();
$searchSource->query($boolQuery);
$searchSource->size(0);

// Aggregation 1: Total sales
$searchSource->aggregation(
    AggregationBuilders::sum('total_sales')
        ->field('total_amount')
);

// Aggregation 2: Average order value
$searchSource->aggregation(
    AggregationBuilders::avg('avg_order_value')
        ->field('total_amount')
);

// Aggregation 3: Sales by month
$salesByMonth = AggregationBuilders::dateHistogram('sales_by_month')
    ->field('order_date')
    ->calendarInterval(DateHistogramBuilder::MONTH);

// Add sub-aggregation for revenue per month
$salesByMonth->subAggregation(
    AggregationBuilders::sum('monthly_revenue')
        ->field('total_amount')
);

// Add the date histogram to the main search
$searchSource->aggregation($salesByMonth);

// Aggregation 4: Top selling products
$searchSource->aggregation(
    AggregationBuilders::terms('top_products')
        ->field('product_name.keyword')
        ->size(10)
);

// Aggregation 5: Sales by category
$salesByCategory = AggregationBuilders::terms('sales_by_category')
    ->field('category')
    ->size(20);

// Add sub-aggregation for revenue per category
$salesByCategory->subAggregation(
    AggregationBuilders::sum('category_revenue')
        ->field('total_amount')
);

// Add the category terms to the main search
$searchSource->aggregation($salesByCategory);

// Execute the search
$request = new SearchRequest('orders');
$request->source($searchSource);
$response = $client->search($request);

// Process aggregation results
$aggs = $response->getAggregations();

// Display the dashboard
echo "Sales Dashboard ($startDate to $endDate)\n";
if (!empty($productCategory)) {
    echo "Category: $productCategory\n";
}
echo "------------------------------------------------\n\n";

// 1. Total sales
$totalSales = $aggs['total_sales']['value'];
echo "Total Sales: $" . number_format($totalSales, 2) . "\n\n";

// 2. Average order value
$avgOrderValue = $aggs['avg_order_value']['value'];
echo "Average Order Value: $" . number_format($avgOrderValue, 2) . "\n\n";

// 3. Monthly sales
echo "Monthly Sales:\n";
foreach ($aggs['sales_by_month']['buckets'] as $bucket) {
    $month = date('F Y', $bucket['key'] / 1000);
    $revenue = $bucket['monthly_revenue']['value'];
    echo "- $month: $" . number_format($revenue, 2) . "\n";
}
echo "\n";

// 4. Top selling products
echo "Top Selling Products:\n";
foreach ($aggs['top_products']['buckets'] as $bucket) {
    $product = $bucket['key'];
    $count = $bucket['doc_count'];
    echo "- $product: $count orders\n";
}
echo "\n";

// 5. Sales by category
echo "Sales by Category:\n";
foreach ($aggs['sales_by_category']['buckets'] as $bucket) {
    $category = $bucket['key'];
    $count = $bucket['doc_count'];
    $revenue = $bucket['category_revenue']['value'];
    echo "- $category: $count orders, $" . number_format($revenue, 2) . " revenue\n";
}
```

## Filtered vs. Global Aggregations

This example demonstrates how to compare filtered results with global results:

```php
<?php
use Zvonchuk\Elastic\Client;
use Zvonchuk\Elastic\Core\SearchRequest;
use Zvonchuk\Elastic\Search\Builder\SearchSourceBuilder;
use Zvonchuk\Elastic\Query\QueryBuilders;
use Zvonchuk\Elastic\Search\Aggregations\AggregationBuilders;

$client = Client::getInstance(['localhost:9200']);

// Filter parameters
$searchTerm = 'smartphone';
$minPrice = 500;

// Main query for filtered results
$boolQuery = QueryBuilders::boolQuery()
    ->must(QueryBuilders::matchQuery('description', $searchTerm))
    ->filter(QueryBuilders::rangeQuery('price')->gte((string)$minPrice));

// Set up search
$searchSource = new SearchSourceBuilder();
$searchSource->query($boolQuery);
$searchSource->size(0);  // No hits, only aggregations

// 1. Filtered aggregations (apply to the query results only)
$searchSource->aggregation(
    AggregationBuilders::terms('filtered_brands')
        ->field('brand')
        ->size(5)
);

$searchSource->aggregation(
    AggregationBuilders::avg('filtered_avg_price')
        ->field('price')
);

// 2. Global aggregations (ignore the query filters)
$globalAvgAgg = AggregationBuilders::global('global_aggs');

// Add sub-aggregations to the global aggregation
$globalAvgAgg->subAggregation(
    AggregationBuilders::avg('global_avg_price')
        ->field('price')
);

$globalBrandsAgg = AggregationBuilders::terms('global_brands')
    ->field('brand')
    ->size(5);

$globalAvgAgg->subAggregation($globalBrandsAgg);

// Add the global aggregation to the search
$searchSource->aggregation($globalAvgAgg);

// 3. Filter-based aggregation (custom filter)
$highEndFilter = QueryBuilders::rangeQuery('price')->gte('1000');
$highEndAgg = AggregationBuilders::filter('high_end_products', $highEndFilter);

$highEndAgg->subAggregation(
    AggregationBuilders::terms('high_end_brands')
        ->field('brand')
        ->size(5)
);

$highEndAgg->subAggregation(
    AggregationBuilders::avg('high_end_avg_price')
        ->field('price')
);

$searchSource->aggregation($highEndAgg);

// Execute the search
$request = new SearchRequest('products');
$request->source($searchSource);
$response = $client->search($request);

// Process aggregation results
$aggs = $response->getAggregations();

// Display comparison of filtered vs. global metrics
echo "Comparison: Products with '$searchTerm' and price ≥ \$$minPrice vs. All Products\n";
echo "----------------------------------------------------------------------\n\n";

// Compare average prices
$filteredAvg = $aggs['filtered_avg_price']['value'];
$globalAvg = $aggs['global_aggs']['global_avg_price']['value'];
$highEndAvg = $aggs['high_end_products']['high_end_avg_price']['value'];

echo "Average Prices:\n";
echo "- Filtered results: $" . number_format($filteredAvg, 2) . "\n";
echo "- All products: $" . number_format($globalAvg, 2) . "\n";
echo "- High-end products (≥ $1000): $" . number_format($highEndAvg, 2) . "\n\n";

// Compare brand distributions
echo "Top Brands (Filtered Results):\n";
foreach ($aggs['filtered_brands']['buckets'] as $bucket) {
    echo "- {$bucket['key']}: {$bucket['doc_count']} products\n";
}

echo "\nTop Brands (All Products):\n";
foreach ($aggs['global_aggs']['global_brands']['buckets'] as $bucket) {
    echo "- {$bucket['key']}: {$bucket['doc_count']} products\n";
}

echo "\nTop Brands (High-End Products):\n";
foreach ($aggs['high_end_products']['high_end_brands']['buckets'] as $bucket) {
    echo "- {$bucket['key']}: {$bucket['doc_count']} products\n";
}
```
