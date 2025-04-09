# Script Sort

Script Sort allows you to sort documents based on custom scripts, enabling complex sorting logic that can't be achieved with field sorts.

## Basic Script Sort

To create a script sort, you need to provide a script that computes a sort value for each document:

```php
<?php
use Zvonchuk\Elastic\Search\Sort\SortBuilders;
use Zvonchuk\Elastic\Search\Sort\ScriptSort;

// Sort by a computed value (price * quantity)
$scriptSort = SortBuilders::scriptSort(
    "doc['price'].value * doc['quantity'].value",
    ScriptSort::NUMBER
);
```

## Specifying Sort Type and Order

You need to specify the type of value the script returns (number or string), and you can specify the sort order:

```php
<?php
use Zvonchuk\Elastic\Search\Sort\SortBuilders;
use Zvonchuk\Elastic\Search\Sort\ScriptSort;
use Zvonchuk\Elastic\Search\Sort\SortBuilder;

// Numeric script sort (descending)
$numericSort = SortBuilders::scriptSort(
    "doc['price'].value * (1 - doc['discount'].value)",
    ScriptSort::NUMBER
)->order(SortBuilder::DESC);

// String script sort (ascending)
$stringSort = SortBuilders::scriptSort(
    "doc['last_name'].value + ', ' + doc['first_name'].value",
    ScriptSort::STRING
)->order(SortBuilder::ASC);
```

## Example: Custom Pricing Sort

Here's an example using script sort to order products by their effective price after discount:

```php
<?php
use Zvonchuk\Elastic\Client;
use Zvonchuk\Elastic\Core\SearchRequest;
use Zvonchuk\Elastic\Search\Builder\SearchSourceBuilder;
use Zvonchuk\Elastic\Query\QueryBuilders;
use Zvonchuk\Elastic\Search\Sort\SortBuilders;
use Zvonchuk\Elastic\Search\Sort\ScriptSort;

$client = Client::getInstance(['localhost:9200']);

// Create a search for electronics products
$searchSource = new SearchSourceBuilder();
$searchSource->query(QueryBuilders::termQuery('category', 'electronics'));

// Sort by effective price (price after discount)
$effectivePriceScript = "doc['price'].value * (1 - (doc['discount_percentage'].value / 100))";
$searchSource->sort(
    SortBuilders::scriptSort($effectivePriceScript, ScriptSort::NUMBER)
);

// Execute the search
$request = new SearchRequest('products');
$request->source($searchSource);
$response = $client->search($request);

// Process results
echo "Electronics sorted by effective price (lowest first):\n";
foreach ($response->getHits() as $hit) {
    $product = $hit['_source'];
    $originalPrice = $product['price'];
    $discount = $product['discount_percentage'];
    $effectivePrice = $originalPrice * (1 - ($discount / 100));
    
    echo "- {$product['name']}\n";
    echo "  Original price: \$$originalPrice\n";
    echo "  Discount: {$discount}%\n";
    echo "  Effective price: \$" . number_format($effectivePrice, 2) . "\n\n";
}
```

## Example: Custom Relevance Sorting

Here's an example that combines multiple factors for custom relevance scoring:

```php
<?php
use Zvonchuk\Elastic\Client;
use Zvonchuk\Elastic\Core\SearchRequest;
use Zvonchuk\Elastic\Search\Builder\SearchSourceBuilder;
use Zvonchuk\Elastic\Query\QueryBuilders;
use Zvonchuk\Elastic\Search\Sort\SortBuilders;
use Zvonchuk\Elastic\Search\Sort\ScriptSort;

$client = Client::getInstance(['localhost:9200']);

// Create a search for products matching a query
$searchSource = new SearchSourceBuilder();
$searchSource->query(
    QueryBuilders::matchQuery('description', 'comfortable chair')
);

// Custom relevance score combining multiple factors:
// - Original relevance score (_score)
// - Product rating (0-5)
// - Recency factor based on creation date
$customRelevanceScript = "
    // Base relevance from text matching
    float relevance = _score;
    
    // Boost by rating (0-5)
    if (doc.containsKey('rating') && doc['rating'].size() > 0) {
        relevance = relevance * (1 + doc['rating'].value / 5);
    }
    
    // Recency boost - newer products rank higher
    if (doc.containsKey('created_at') && doc['created_at'].size() > 0) {
        long now = new Date().getTime();
        long created = doc['created_at'].value.getMillis();
        long ageInDays = (now - created) / 86400000;
        
        // Gradually reduce score for older products (max age factor: 365 days)
        float ageFactor = ageInDays > 365 ? 0 : (365 - ageInDays) / 365;
        relevance = relevance * (0.7 + 0.3 * ageFactor);
    }
    
    return relevance;
";

// Sort by our custom relevance score
$searchSource->sort(
    SortBuilders::scriptSort($customRelevanceScript, ScriptSort::NUMBER)
        ->order('desc')
);

// Execute the search
$request = new SearchRequest('products');
$request->source($searchSource);
$response = $client->search($request);

// Process results
echo "Products sorted by custom relevance:\n";
foreach ($response->getHits() as $hit) {
    $product = $hit['_source'];
    echo "- {$product['name']}\n";
    echo "  Rating: {$product['rating']}/5\n";
    echo "  Created: {$product['created_at']}\n";
    echo "  Original score: {$hit['_score']}\n";
    echo "  Custom sort value: {$hit['sort'][0]}\n\n";
}
```

## Script Sort Performance Considerations

Script sorting can be resource-intensive and may impact performance. Consider these tips:

1. Keep scripts as simple as possible
2. Cache frequently used scripts
3. Use stored fields when possible
4. For very large result sets, consider using field sorts instead
