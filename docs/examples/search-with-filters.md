# Search with Filters

This example demonstrates how to create a search with multiple filters, similar to what you might use in an e-commerce product search.

## Product Search with Multiple Filters

```php
<?php
use Zvonchuk\Elastic\Client;
use Zvonchuk\Elastic\Core\SearchRequest;
use Zvonchuk\Elastic\Search\Builder\SearchSourceBuilder;
use Zvonchuk\Elastic\Query\QueryBuilders;
use Zvonchuk\Elastic\Search\Sort\SortBuilders;

$client = Client::getInstance(['localhost:9200']);

// User input parameters
$searchTerm = 'laptop';
$category = 'electronics';
$minPrice = 500;
$maxPrice = 2000;
$brands = ['Apple', 'Dell', 'HP'];
$inStock = true;
$page = 1;
$perPage = 20;

// Create bool query for filtering
$boolQuery = QueryBuilders::boolQuery();

// Add full-text search if a search term is provided
if (!empty($searchTerm)) {
    $boolQuery->must(
        QueryBuilders::matchQuery('name', $searchTerm)
            ->operator('OR')
            ->fuzziness('AUTO')
    );
}

// Add category filter
if (!empty($category)) {
    $boolQuery->filter(
        QueryBuilders::termQuery('category', $category)
    );
}

// Add price range filter
if ($minPrice > 0 || $maxPrice > 0) {
    $rangeQuery = QueryBuilders::rangeQuery('price');
    
    if ($minPrice > 0) {
        $rangeQuery->gte((string)$minPrice);
    }
    
    if ($maxPrice > 0) {
        $rangeQuery->lte((string)$maxPrice);
    }
    
    $boolQuery->filter($rangeQuery);
}

// Add brands filter (if any brands are selected)
if (!empty($brands)) {
    $boolQuery->filter(
        QueryBuilders::termsQuery('brand', $brands)
    );
}

// Add in-stock filter
if ($inStock) {
    $boolQuery->filter(
        QueryBuilders::termQuery('in_stock', true)
    );
}

// Set up the search source with pagination
$from = ($page - 1) * $perPage;

$searchSource = new SearchSourceBuilder();
$searchSource->query($boolQuery);
$searchSource->from($from);
$searchSource->size($perPage);

// Add sorting (relevance if search term provided, otherwise price)
if (!empty($searchTerm)) {
    // Default sort is by relevance (_score)
} else {
    $searchSource->sort(
        SortBuilders::fieldSort('price')->order('asc')
    );
}

// Execute the search
$request = new SearchRequest('products');
$request->source($searchSource);
$response = $client->search($request);

// Process results
$total = $response->getTotal();
$hits = $response->getHits();

echo "Found $total products matching your criteria.\n";
echo "Showing " . count($hits) . " products (page $page):\n\n";

foreach ($hits as $hit) {
    $product = $hit['_source'];
    
    echo "- {$product['name']}\n";
    echo "  Brand: {$product['brand']}\n";
    echo "  Price: \${$product['price']}\n";
    echo "  In Stock: " . ($product['in_stock'] ? "Yes" : "No") . "\n";
    
    if (isset($hit['_score'])) {
        echo "  Relevance Score: {$hit['_score']}\n";
    }
    
    echo "\n";
}
```

## Faceted Navigation with Filters

This example extends the previous one by adding aggregations to build faceted navigation:

```php
<?php
use Zvonchuk\Elastic\Client;
use Zvonchuk\Elastic\Core\SearchRequest;
use Zvonchuk\Elastic\Search\Builder\SearchSourceBuilder;
use Zvonchuk\Elastic\Query\QueryBuilders;
use Zvonchuk\Elastic\Search\Aggregations\AggregationBuilders;

$client = Client::getInstance(['localhost:9200']);

// User input parameters
$searchTerm = 'laptop';
$selectedCategory = 'electronics';
$selectedBrands = ['Apple'];  // Currently selected brands
$minPrice = 500;
$maxPrice = 2000;

// Build the main query
$boolQuery = QueryBuilders::boolQuery();

// Full-text search
if (!empty($searchTerm)) {
    $boolQuery->must(
        QueryBuilders::matchQuery('name', $searchTerm)
            ->operator('OR')
    );
}

// Category filter
if (!empty($selectedCategory)) {
    $boolQuery->filter(
        QueryBuilders::termQuery('category', $selectedCategory)
    );
}

// Brand filter
if (!empty($selectedBrands)) {
    $boolQuery->filter(
        QueryBuilders::termsQuery('brand', $selectedBrands)
    );
}

// Price range filter
$rangeQuery = QueryBuilders::rangeQuery('price');
if ($minPrice > 0) {
    $rangeQuery->gte((string)$minPrice);
}
if ($maxPrice > 0) {
    $rangeQuery->lte((string)$maxPrice);
}
$boolQuery->filter($rangeQuery);

// Set up search
$searchSource = new SearchSourceBuilder();
$searchSource->query($boolQuery);
$searchSource->size(20);  // Product results to show

// Aggregations for faceted navigation

// 1. Categories aggregation
$searchSource->aggregation(
    AggregationBuilders::terms('categories')
        ->field('category')
        ->size(10)
);

// 2. Brands aggregation
$searchSource->aggregation(
    AggregationBuilders::terms('brands')
        ->field('brand')
        ->size(20)
);

// 3. Price ranges aggregation
$searchSource->aggregation(
    AggregationBuilders::histogram('price_ranges')
        ->field('price')
        ->interval(500)  // $500 intervals
        ->minDocCount(1)
);

// 4. Average price aggregation
$searchSource->aggregation(
    AggregationBuilders::avg('avg_price')
        ->field('price')
);

// Execute the search
$request = new SearchRequest('products');
$request->source($searchSource);
$response = $client->search($request);

// Process results
$hits = $response->getHits();
$total = $response->getTotal();
$aggregations = $response->getAggregations();

// Display product results
echo "Found $total products matching your criteria.\n\n";

foreach ($hits as $hit) {
    $product = $hit['_source'];
    echo "- {$product['name']} (\${$product['price']})\n";
}

// Display facets for further filtering

// 1. Categories facet
echo "\nCategories:\n";
foreach ($aggregations['categories']['buckets'] as $bucket) {
    $isSelected = ($bucket['key'] === $selectedCategory) ? ' (selected)' : '';
    echo "- {$bucket['key']}: {$bucket['doc_count']}$isSelected\n";
}

// 2. Brands facet
echo "\nBrands:\n";
foreach ($aggregations['brands']['buckets'] as $bucket) {
    $isSelected = in_array($bucket['key'], $selectedBrands) ? ' (selected)' : '';
    echo "- {$bucket['key']}: {$bucket['doc_count']}$isSelected\n";
}

// 3. Price ranges facet
echo "\nPrice Ranges:\n";
foreach ($aggregations['price_ranges']['buckets'] as $bucket) {
    $min = $bucket['key'];
    $max = $bucket['key'] + 500;
    echo "- \$$min - \$$max: {$bucket['doc_count']}\n";
}

// 4. Average price
$avgPrice = $aggregations['avg_price']['value'];
echo "\nAverage Price: \$" . number_format($avgPrice, 2) . "\n";
```
