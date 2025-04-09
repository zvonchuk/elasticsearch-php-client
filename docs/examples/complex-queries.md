# Complex Queries

This example demonstrates how to build complex, multi-level queries for advanced search scenarios.

## Advanced Product Search with User Preferences

This example shows a complex search that combines various query types with weighted preferences:

```php
<?php
use Zvonchuk\Elastic\Client;
use Zvonchuk\Elastic\Core\SearchRequest;
use Zvonchuk\Elastic\Search\Builder\SearchSourceBuilder;
use Zvonchuk\Elastic\Query\QueryBuilders;
use Zvonchuk\Elastic\Search\Sort\SortBuilders;

$client = Client::getInstance(['localhost:9200']);

// User parameters
$searchTerm = 'wireless headphones';
$userLocation = [
    'lat' => 40.7128,
    'lon' => -74.0060
];
$preferredBrands = ['Sony', 'Bose', 'Sennheiser'];
$priceRange = [50, 350];
$minRating = 4.0;

// User preferences weighting
$preferNewArrivals = true;
$preferLocalStock = true;

// Build the main query
$mainQuery = QueryBuilders::boolQuery();

// 1. Must conditions (required matches)
if (!empty($searchTerm)) {
    // Match title with higher weight
    $mainQuery->should(
        QueryBuilders::matchQuery('title', $searchTerm)
            ->operator('AND')
            ->fuzziness('AUTO')
    );
    
    // Match description with lower weight
    $mainQuery->should(
        QueryBuilders::matchQuery('description', $searchTerm)
            ->operator('OR')
    );
    
    // Require at least one should clause to match
    $mainQuery->minimumShouldMatch(1);
}

// 2. Filter conditions (don't affect score, but must match)
// Price range filter
if (!empty($priceRange)) {
    $mainQuery->filter(
        QueryBuilders::rangeQuery('price')
            ->gte((string)$priceRange[0])
            ->lte((string)$priceRange[1])
    );
}

// Rating filter
if ($minRating > 0) {
    $mainQuery->filter(
        QueryBuilders::rangeQuery('rating')
            ->gte((string)$minRating)
    );
}

// 3. Should conditions (boost score if matched)
// Preferred brands (boost score)
if (!empty($preferredBrands)) {
    $mainQuery->should(
        QueryBuilders::termsQuery('brand', $preferredBrands)
    );
}

// Prefer new arrivals
if ($preferNewArrivals) {
    // Last 30 days items get a boost
    $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));
    $mainQuery->should(
        QueryBuilders::rangeQuery('created_at')
            ->gte($thirtyDaysAgo)
    );
}

// Prefer local stock if user location provided
if ($preferLocalStock && !empty($userLocation)) {
    // Boost items available in stores within 10 miles
    $mainQuery->should(
        QueryBuilders::GeoDistanceQuery('store_location')
            ->point($userLocation['lat'], $userLocation['lon'])
            ->distance('10mi')
    );
}

// Set up the search
$searchSource = new SearchSourceBuilder();
$searchSource->query($mainQuery);
$searchSource->size(20);

// Add sorting (by relevance, then by distance if location provided)
if (!empty($userLocation)) {
    $searchSource->sort(
        SortBuilders::geoDistanceSort('store_location', $userLocation['lat'], $userLocation['lon'])
            ->unit('mi')
    );
}

// Execute the search
$request = new SearchRequest('products');
$request->source($searchSource);
$response = $client->search($request);

// Process results
$hits = $response->getHits();
echo "Found " . count($hits) . " matching products:\n\n";

foreach ($hits as $hit) {
    $product = $hit['_source'];
    $score = $hit['_score'];
    $distance = isset($hit['sort'][0]) ? round($hit['sort'][0], 1) . ' miles' : 'N/A';
    
    echo "- {$product['title']} by {$product['brand']}\n";
    echo "  Price: \${$product['price']}, Rating: {$product['rating']}/5\n";
    echo "  Relevance Score: $score, Distance to nearest store: $distance\n";
    echo "  {$product['description']}\n\n";
}
```

## E-commerce Search with Learning to Rank Features

This example demonstrates a complex search that could be used with a learning-to-rank system:

```php
<?php
use Zvonchuk\Elastic\Client;
use Zvonchuk\Elastic\Core\SearchRequest;
use Zvonchuk\Elastic\Search\Builder\SearchSourceBuilder;
use Zvonchuk\Elastic\Query\QueryBuilders;

$client = Client::getInstance(['localhost:9200']);

// User input and context
$query = 'lightweight laptop';
$userId = '12345';
$userPreferences = [
    'brands' => ['Apple', 'Dell', 'Lenovo'],
    'priceRange' => [800, 2000],
    'categories' => ['laptops', 'ultrabooks']
];
$userHistory = [
    'recent_views' => ['product_123', 'product_456'],
    'recent_purchases' => ['product_789']
];
$seasonalBoosts = [
    'isBackToSchool' => true,
    'isHolidaySeason' => false
];

// Build the main query
$mainQuery = QueryBuilders::boolQuery();

// 1. Text matching component (full-text search)
$textMatchQuery = QueryBuilders::boolQuery();

// Title matching with high boost
$textMatchQuery->should(
    QueryBuilders::matchQuery('title', $query)
        ->operator('OR')
        ->fuzziness('AUTO')
);

// Description matching with medium boost
$textMatchQuery->should(
    QueryBuilders::matchQuery('description', $query)
        ->operator('OR')
);

// Keywords matching
$textMatchQuery->should(
    QueryBuilders::matchQuery('keywords', $query)
        ->operator('OR')
);

// Add the text matching component to main query
$mainQuery->must($textMatchQuery);

// 2. Filtering component (non-scoring)
// Price range filter
if (!empty($userPreferences['priceRange'])) {
    $mainQuery->filter(
        QueryBuilders::rangeQuery('price')
            ->gte((string)$userPreferences['priceRange'][0])
            ->lte((string)$userPreferences['priceRange'][1])
    );
}

// Only show in-stock items
$mainQuery->filter(
    QueryBuilders::termQuery('in_stock', true)
);

// 3. Personalization component (boosting)
// Boost preferred brands
if (!empty($userPreferences['brands'])) {
    $mainQuery->should(
        QueryBuilders::termsQuery('brand', $userPreferences['brands'])
    );
}

// Boost preferred categories
if (!empty($userPreferences['categories'])) {
    $mainQuery->should(
        QueryBuilders::termsQuery('category', $userPreferences['categories'])
    );
}

// Boost recently viewed products (except ones already purchased)
if (!empty($userHistory['recent_views'])) {
    $recentViewsQuery = QueryBuilders::boolQuery()
        ->should(QueryBuilders::termsQuery('product_id', $userHistory['recent_views']));
    
    // Exclude recently purchased items
    if (!empty($userHistory['recent_purchases'])) {
        $recentViewsQuery->mustNot(
            QueryBuilders::termsQuery('product_id', $userHistory['recent_purchases'])
        );
    }
    
    $mainQuery->should($recentViewsQuery);
}

// 4. Business rules component
// Boost new arrivals
$thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));
$mainQuery->should(
    QueryBuilders::rangeQuery('created_at')
        ->gte($thirtyDaysAgo)
);

// Boost sale items
$mainQuery->should(
    QueryBuilders::termQuery('on_sale', true)
);

// Seasonal boosts
if ($seasonalBoosts['isBackToSchool']) {
    $mainQuery->should(
        QueryBuilders::termQuery('tags', 'back_to_school')
    );
}

if ($seasonalBoosts['isHolidaySeason']) {
    $mainQuery->should(
        QueryBuilders::termQuery('tags', 'holiday_deal')
    );
}

// 5. Social proof component
// Boost highly rated products
$mainQuery->should(
    QueryBuilders::rangeQuery('rating')
        ->gte('4.5')
);

// Boost frequently purchased products
$mainQuery->should(
    QueryBuilders::rangeQuery('purchase_count')
        ->gte('100')
);

// Set up the search with explain to see scoring details
$searchSource = new SearchSourceBuilder();
$searchSource->query($mainQuery);
$searchSource->size(20);
$searchSource->explain(true);  // Include score explanation

// Execute the search
$request = new SearchRequest('products');
$request->source($searchSource);
$response = $client->search($request);

// Process results
$hits = $response->getHits();
echo "Search results for '$query':\n\n";

foreach ($hits as $hit) {
    $product = $hit['_source'];
    $score = $hit['_score'];
    
    echo "- {$product['title']} ({$product['brand']})\n";
    echo "  Price: \${$product['price']}, Rating: {$product['rating']}/5\n";
    echo "  Score: $score\n";
    
    if (isset($hit['_explanation'])) {
        echo "  Top scoring factors:\n";
        printTopScoringFactors($hit['_explanation'], 2);
    }
    
    echo "\n";
}

// Helper function to print top scoring factors
function printTopScoringFactors($explanation, $maxDepth, $depth = 0) {
    if ($depth >= $maxDepth) return;
    
    if (isset($explanation['description'])) {
        $indent = str_repeat("  ", $depth + 1);
        echo $indent . "- " . $explanation['description'] . " (score: {$explanation['value']})\n";
    }
    
    if (isset($explanation['details']) && is_array($explanation['details'])) {
        // Sort details by value (score) in descending order
        usort($explanation['details'], function($a, $b) {
            return $b['value'] <=> $a['value'];
        });
        
        // Print top 3 details
        $topDetails = array_slice($explanation['details'], 0, 3);
        foreach ($topDetails as $detail) {
            printTopScoringFactors($detail, $maxDepth, $depth + 1);
        }
    }
}
```
