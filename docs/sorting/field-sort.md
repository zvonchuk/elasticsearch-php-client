# Field Sort

Field sort allows you to sort documents based on values in a specific field.

## Basic Field Sort

To sort by a specific field in ascending order:

```php
<?php
use Zvonchuk\Elastic\Search\Sort\SortBuilders;

$sort = SortBuilders::fieldSort('price');
```

## Specifying Sort Order

You can specify ascending or descending order:

```php
<?php
use Zvonchuk\Elastic\Search\Sort\SortBuilders;
use Zvonchuk\Elastic\Search\Sort\SortBuilder;

// Ascending order (default)
$ascendingSort = SortBuilders::fieldSort('price')
    ->order(SortBuilder::ASC);

// Descending order
$descendingSort = SortBuilders::fieldSort('created_at')
    ->order(SortBuilder::DESC);
```

## Sorting by Multiple Fields

To create a multi-field sort, add multiple sort criteria to your search:

```php
<?php
use Zvonchuk\Elastic\Search\Builder\SearchSourceBuilder;
use Zvonchuk\Elastic\Search\Sort\SortBuilders;
use Zvonchuk\Elastic\Search\Sort\SortBuilder;

$searchSource = new SearchSourceBuilder();

// First sort by category (ascending)
$searchSource->sort(SortBuilders::fieldSort('category'));

// Then by price (ascending)
$searchSource->sort(SortBuilders::fieldSort('price'));
```

## Sorting on Text Fields

When sorting on text fields, you should typically use the keyword version:

```php
<?php
use Zvonchuk\Elastic\Search\Sort\SortBuilders;

// Sort using the keyword field
$sort = SortBuilders::fieldSort('title.keyword');
```

## Example: Product Search with Sorting

Here's a complete example of a product search with field sorting:

```php
<?php
use Zvonchuk\Elastic\Client;
use Zvonchuk\Elastic\Core\SearchRequest;
use Zvonchuk\Elastic\Search\Builder\SearchSourceBuilder;
use Zvonchuk\Elastic\Query\QueryBuilders;
use Zvonchuk\Elastic\Search\Sort\SortBuilders;
use Zvonchuk\Elastic\Search\Sort\SortBuilder;

$client = Client::getInstance(['localhost:9200']);

// Create a search for electronics products
$boolQuery = QueryBuilders::boolQuery()
    ->must(QueryBuilders::termQuery('category', 'electronics'))
    ->filter(QueryBuilders::rangeQuery('price')->lte('1000'));

$searchSource = new SearchSourceBuilder();
$searchSource->query($boolQuery);

// Sort first by price (low to high)
$searchSource->sort(
    SortBuilders::fieldSort('price')->order(SortBuilder::ASC)
);

// Then by rating (high to low)
$searchSource->sort(
    SortBuilders::fieldSort('rating')->order(SortBuilder::DESC)
);

// Execute the search
$request = new SearchRequest('products');
$request->source($searchSource);
$response = $client->search($request);

// Process results
echo "Electronics under $1000, sorted by price (lowest first) and then rating (highest first):\n";
foreach ($response->getHits() as $hit) {
    $product = $hit['_source'];
    echo "- {$product['name']}: \${$product['price']}, Rating: {$product['rating']}\n";
}
```

## Example: Pagination with Sorting

When implementing pagination with sorting, you need to keep the sort consistent:

```php
<?php
use Zvonchuk\Elastic\Client;
use Zvonchuk\Elastic\Core\SearchRequest;
use Zvonchuk\Elastic\Search\Builder\SearchSourceBuilder;
use Zvonchuk\Elastic\Query\QueryBuilders;
use Zvonchuk\Elastic\Search\Sort\SortBuilders;

$client = Client::getInstance(['localhost:9200']);

// Page parameters
$page = 2;  // Page number (1-based)
$perPage = 10;  // Items per page

// Calculate from value
$from = ($page - 1) * $perPage;

// Create search
$searchSource = new SearchSourceBuilder();
$searchSource->query(QueryBuilders::matchAllQuery());
$searchSource->size($perPage);
$searchSource->from($from);

// Add consistent sorting
$searchSource->sort(SortBuilders::fieldSort('created_at')->order('desc'));
$searchSource->sort(SortBuilders::fieldSort('_id')->order('asc'));  // Tie-breaker

// Execute the search
$request = new SearchRequest('products');
$request->source($searchSource);
$response = $client->search($request);

// Display results
echo "Page $page (items " . ($from + 1) . "-" . ($from + $perPage) . "):\n";
foreach ($response->getHits() as $index => $hit) {
    $itemNumber = $from + $index + 1;
    echo "$itemNumber. {$hit['_source']['name']}\n";
}
```

## Using Sort with Search After

For deep pagination, you can use the search_after parameter with sort values:

```php
<?php
use Zvonchuk\Elastic\Client;
use Zvonchuk\Elastic\Core\SearchRequest;
use Zvonchuk\Elastic\Search\Builder\SearchSourceBuilder;
use Zvonchuk\Elastic\Query\QueryBuilders;
use Zvonchuk\Elastic\Search\Sort\SortBuilders;

$client = Client::getInstance(['localhost:9200']);

// Create search
$searchSource = new SearchSourceBuilder();
$searchSource->query(QueryBuilders::matchAllQuery());
$searchSource->size(10);

// Add consistent sorting
$searchSource->sort(SortBuilders::fieldSort('created_at')->order('desc'));
$searchSource->sort(SortBuilders::fieldSort('_id')->order('asc'));  // Tie-breaker

// Add search_after from previous page's last hit
$lastSortValues = ['2023-01-15T10:30:00Z', 'product_567'];
$searchSource->searchAfter($lastSortValues);

// Execute the search
$request = new SearchRequest('products');
$request->source($searchSource);
$response = $client->search($request);

// Process and display results
foreach ($response->getHits() as $hit) {
    echo "{$hit['_source']['name']} - {$hit['_source']['created_at']}\n";
    
    // Save the sort values of the last item for the next page
    $lastSortValues = $hit['sort'];
}
echo "Next page search_after values: " . json_encode($lastSortValues) . "\n";
```
